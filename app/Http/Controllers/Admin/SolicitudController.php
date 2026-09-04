<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    private const BUFFER_AFTER_MINUTES = 60;

    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(): View
    {
        $reservas = Reserva::query()
            ->with('user')
            ->where('estado', Reserva::ESTADO_PENDIENTE)
            ->orderBy('start_time')
            ->paginate(10);

        $proximosEventos = Reserva::query()
            ->where('estado', Reserva::ESTADO_APROBADA)
            ->whereDate('start_time', '>=', Carbon::today())
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        return view('dashboards.admin', compact('reservas', 'proximosEventos'));
    }

    public function historial(Request $request): View
    {
        $tab = $request->input('tab', 'vigentes');

        $query = Reserva::query()
            ->with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('nombre_evento', 'like', "%{$search}%")
                        ->orWhere('motivo', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($userQ) => $userQ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('estado') && $request->input('estado') !== 'todos', function ($q) use ($request) {
                $q->where('estado', $request->input('estado'));
            });

        if ($tab === 'vigentes') {
            $query->where(function ($q) {
                $q->whereDate('start_time', '>=', Carbon::today())
                    ->orWhere('estado', Reserva::ESTADO_PENDIENTE);
            })->orderBy('start_time', 'asc');
        } else {
            $query->whereDate('start_time', '<', Carbon::today())
                ->where('estado', '!=', Reserva::ESTADO_PENDIENTE)
                ->orderBy('start_time', 'desc');
        }

        $reservas = $query->paginate(15)->withQueryString();

        return view('admin.historial', compact('reservas', 'tab'));
    }

    public function aprobar(Request $request, Reserva $reserva): RedirectResponse
    {
        abort_unless($reserva->estado === Reserva::ESTADO_PENDIENTE, 409, 'La solicitud ya no está pendiente.');

        DB::transaction(function () use ($request, $reserva): void {
            $reserva->update(['estado' => Reserva::ESTADO_APROBADA]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'aprobar_reserva',
                modulo: 'Solicitudes',
                descripcion: "Aprobó la solicitud de reserva #{$reserva->id}.",
                nivel: AuditLogger::NIVEL_IMPORTANTE,
                sujeto: $reserva,
                valoresAnteriores: ['estado' => Reserva::ESTADO_PENDIENTE],
                valoresNuevos: ['estado' => Reserva::ESTADO_APROBADA],
            );
        });

        return back()->with('success', 'La solicitud fue aprobada correctamente.');
    }

    public function rechazar(Request $request, Reserva $reserva): RedirectResponse
    {
        $datos = $request->validate([
            'nota_admin' => ['required', 'string', 'max:500'],
        ], [
            'nota_admin.required' => 'Debes indicar el motivo del rechazo.',
            'nota_admin.max' => 'El motivo del rechazo no puede superar los 500 caracteres.',
        ]);

        abort_unless($reserva->estado === Reserva::ESTADO_PENDIENTE, 409, 'La solicitud ya no está pendiente.');

        DB::transaction(function () use ($request, $reserva, $datos): void {
            $reserva->update([
                'estado' => Reserva::ESTADO_RECHAZADA,
                'nota_admin' => $datos['nota_admin'],
            ]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'rechazar_reserva',
                modulo: 'Solicitudes',
                descripcion: "Rechazó la solicitud de reserva #{$reserva->id}.",
                nivel: AuditLogger::NIVEL_IMPORTANTE,
                sujeto: $reserva,
                valoresAnteriores: ['estado' => Reserva::ESTADO_PENDIENTE, 'nota_admin' => null],
                valoresNuevos: ['estado' => Reserva::ESTADO_RECHAZADA, 'nota_admin' => $datos['nota_admin']],
            );
        });

        return back()->with('success', 'La solicitud fue rechazada correctamente.');
    }

    public function update(Request $request, Reserva $reserva): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_evento' => ['required', 'string', 'max:150'],
            'fecha' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'motivo' => ['required', 'string', 'max:500'],
            'necesidades' => ['nullable', 'string', 'max:1000'],
        ]);

        $fecha = Carbon::parse($validated['fecha']);
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $fecha->format('Y-m-d') . ' ' . $validated['start_time']);
        $endTime = Carbon::createFromFormat('Y-m-d H:i', $fecha->format('Y-m-d') . ' ' . $validated['end_time']);

        if ($this->hayConflicto($startTime, $endTime, $reserva->id)) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario seleccionado choca con otro evento existente o su margen de limpieza.',
            ]);
        }

        DB::transaction(function () use ($request, $reserva, $validated, $startTime, $endTime): void {
            $reserva->update([
                'nombre_evento' => $validated['nombre_evento'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'motivo' => $validated['motivo'],
                'necesidades' => $validated['necesidades'] ?? null,
            ]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'editar_reserva_admin',
                modulo: 'Solicitudes',
                descripcion: "Modificó administrativamente la reserva #{$reserva->id}.",
                nivel: AuditLogger::NIVEL_IMPORTANTE,
                sujeto: $reserva,
            );
        });

        return back()->with('success', 'La reserva fue actualizada correctamente.');
    }

    public function destroy(Request $request, Reserva $reserva): RedirectResponse
    {
        DB::transaction(function () use ($request, $reserva): void {
            $reserva->delete();

            $this->auditLogger->registrar(
                request: $request,
                accion: 'eliminar_reserva_admin',
                modulo: 'Solicitudes',
                descripcion: "Eliminó la reserva o bloqueo #{$reserva->id}.",
                nivel: AuditLogger::NIVEL_CRITICA,
            );
        });

        return back()->with('success', 'El registro fue eliminado permanentemente.');
    }

    private function hayConflicto(Carbon $startTime, Carbon $endTime, ?int $reservaId = null): bool
    {
        $nuevoFinConLimpieza = $endTime->copy()->addMinutes(self::BUFFER_AFTER_MINUTES);

        return Reserva::query()
            ->when($reservaId !== null, fn($query) => $query->where('id', '!=', $reservaId))
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_APROBADA])
            ->where(function ($query) use ($startTime, $nuevoFinConLimpieza) {
                $query->where('start_time', '<', $nuevoFinConLimpieza)
                    ->where('end_time', '>', $startTime->copy()->subMinutes(self::BUFFER_AFTER_MINUTES));
            })->exists();
    }
}
