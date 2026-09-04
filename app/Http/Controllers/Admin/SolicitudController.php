<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SolicitudController extends Controller
{
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
            ->where('start_time', '>=', now()->startOfDay())
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        return view('dashboards.admin', compact('reservas', 'proximosEventos'));
    }

    public function historial(Request $request): View
    {
        $reservas = Reserva::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('nombre_evento', 'like', "%{$search}%")
                        ->orWhere('motivo', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($userQ) => $userQ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('estado') && $request->input('estado') !== 'todos', function ($query) use ($request) {
                $query->where('estado', $request->input('estado'));
            })
            ->orderByDesc('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.historial', compact('reservas'));
    }

    public function aprobar(Request $request, Reserva $reserva): RedirectResponse
    {
        abort_unless(
            $reserva->estado === Reserva::ESTADO_PENDIENTE,
            409,
            'La solicitud ya no está pendiente.'
        );

        DB::transaction(function () use ($request, $reserva): void {
            $reserva->update([
                'estado' => Reserva::ESTADO_APROBADA,
            ]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'aprobar_reserva',
                modulo: 'Solicitudes',
                descripcion: "Aprobó la solicitud de reserva #{$reserva->id}.",
                nivel: AuditLogger::NIVEL_IMPORTANTE,
                sujeto: $reserva,
                valoresAnteriores: [
                    'estado' => Reserva::ESTADO_PENDIENTE,
                ],
                valoresNuevos: [
                    'estado' => Reserva::ESTADO_APROBADA,
                ],
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

        abort_unless(
            $reserva->estado === Reserva::ESTADO_PENDIENTE,
            409,
            'La solicitud ya no está pendiente.'
        );

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
                valoresAnteriores: [
                    'estado' => Reserva::ESTADO_PENDIENTE,
                    'nota_admin' => null,
                ],
                valoresNuevos: [
                    'estado' => Reserva::ESTADO_RECHAZADA,
                    'nota_admin' => $datos['nota_admin'],
                ],
            );
        });

        return back()->with('success', 'La solicitud fue rechazada correctamente.');
    }
}
