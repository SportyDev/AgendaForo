<?php

namespace App\Http\Controllers\Solicitante;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservaRequest;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function index(): View
    {
        return view('dashboards.solicitante');
    }

    public function getEventos(): JsonResponse
    {
        $reservas = Reserva::query()
            ->where(function ($query) {
                $query
                    ->where('estado', Reserva::ESTADO_APROBADA)
                    ->orWhere(function ($query) {
                        $query
                            ->where('estado', Reserva::ESTADO_PENDIENTE)
                            ->where('user_id', Auth::id());
                    });
            })
            ->orderBy('start_time')
            ->get();

        $eventos = $reservas->map(function (Reserva $reserva) {
            $esPendiente = $reserva->estado === Reserva::ESTADO_PENDIENTE;

            return [
                'title' => $esPendiente ? 'Mi solicitud pendiente' : 'Auditorio reservado',
                'start' => $reserva->start_time->toIso8601String(),
                'end' => $reserva->end_time->toIso8601String(),
                'color' => $esPendiente ? '#F59E0B' : '#2563EB',
            ];
        })->values();

        return response()->json($eventos);
    }

    public function store(ReservaRequest $request): RedirectResponse
    {
        $fecha = Carbon::parse($request->validated('fecha'));

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $fecha->format('Y-m-d') . ' ' . $request->validated('start_time')
        );

        $endTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $fecha->format('Y-m-d') . ' ' . $request->validated('end_time')
        );

        $hayConflicto = Reserva::query()
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_APROBADA])
            ->where(function ($query) use ($startTime, $endTime) {
                $query
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($hayConflicto) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario seleccionado ya está ocupado. Elige otra fecha u horario.',
            ]);
        }

        Reserva::create([
            'user_id' => Auth::id(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'motivo' => $request->validated('motivo'),
            'necesidades' => $request->validated('necesidades'),
            'estado' => Reserva::ESTADO_PENDIENTE,
        ]);

        return back()->with(
            'success',
            'Tu solicitud de reserva fue enviada correctamente y quedó pendiente de aprobación.'
        );
    }

    public function historial(): View
    {
        $reservas = Reserva::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('solicitante.reservas', compact('reservas'));
    }

    public function update(Request $request, Reserva $reserva): RedirectResponse
    {
        if ($reserva->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reserva->estado !== Reserva::ESTADO_PENDIENTE) {
            return back()->withErrors([
                'reserva' => 'Solo puedes editar solicitudes que estén pendientes.',
            ]);
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'motivo' => ['required', 'string', 'max:500'],
            'necesidades' => ['nullable', 'string', 'max:1000'],
        ]);

        $fecha = Carbon::parse($validated['fecha']);

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $fecha->format('Y-m-d') . ' ' . $validated['start_time']
        );

        $endTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $fecha->format('Y-m-d') . ' ' . $validated['end_time']
        );

        $hayConflicto = Reserva::query()
            ->where('id', '!=', $reserva->id)
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_APROBADA])
            ->where(function ($query) use ($startTime, $endTime) {
                $query
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($hayConflicto) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario seleccionado ya está ocupado. Elige otra fecha u horario.',
            ]);
        }

        $reserva->update([
            'start_time' => $startTime,
            'end_time' => $endTime,
            'motivo' => $validated['motivo'],
            'necesidades' => $validated['necesidades'] ?? null,
        ]);

        return back()->with('success', 'La reserva fue actualizada exitosamente.');
    }

    public function destroy(Reserva $reserva): RedirectResponse
    {
        if ($reserva->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reserva->estado !== Reserva::ESTADO_PENDIENTE) {
            return back()->withErrors([
                'reserva' => 'Solo puedes cancelar solicitudes que estén pendientes.',
            ]);
        }

        $reserva->delete();

        return back()->with('success', 'Reserva cancelada exitosamente.');
    }
}
