<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    // Calendario global del administrador.
    public function index(): View
    {
        return view('admin.calendario');
    }

    // Devuelve todas las reservas pendientes y aprobadas.
    public function getEventos(): JsonResponse
    {
        $reservas = Reserva::query()
            ->with('user')
            ->whereIn('estado', [
                Reserva::ESTADO_PENDIENTE,
                Reserva::ESTADO_APROBADA,
            ])
            ->orderBy('start_time')
            ->get();

        $eventos = $reservas->map(function (Reserva $reserva): array {
            $estado = match ($reserva->estado) {
                Reserva::ESTADO_APROBADA => 'Aprobada',
                Reserva::ESTADO_PENDIENTE => 'Pendiente',
                default => 'Reserva',
            };

            return [
                'id' => $reserva->id,
                'title' => $estado . ' - ' . ($reserva->user?->name ?? 'Usuario'),
                'start' => $reserva->start_time->toIso8601String(),
                'end' => $reserva->end_time->toIso8601String(),
                'color' => $reserva->estado === Reserva::ESTADO_APROBADA
                    ? '#2563EB'
                    : '#F59E0B',
                'textColor' => '#FFFFFF',
            ];
        })->values();

        return response()->json($eventos);
    }

    // Registra un bloqueo manual como una reserva aprobada.
    public function bloquear(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'motivo' => ['required', 'string', 'max:500'],
        ], [
            'fecha.required' => 'Selecciona una fecha.',
            'start_time.required' => 'Selecciona la hora de inicio.',
            'end_time.required' => 'Selecciona la hora de término.',
            'end_time.after' => 'La hora de término debe ser posterior a la hora de inicio.',
            'motivo.required' => 'Escribe el motivo del bloqueo.',
            'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
        ]);

        $fecha = Carbon::parse($datos['fecha']);

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $fecha->format('Y-m-d') . ' ' . $datos['start_time']
        );

        $endTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $fecha->format('Y-m-d') . ' ' . $datos['end_time']
        );

        $hayConflicto = Reserva::query()
            ->whereIn('estado', [
                Reserva::ESTADO_PENDIENTE,
                Reserva::ESTADO_APROBADA,
            ])
            ->where(function ($query) use ($startTime, $endTime): void {
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

        $reserva = DB::transaction(function () use (
            $request,
            $datos,
            $startTime,
            $endTime
        ): Reserva {
            $reserva = Reserva::create([
                'user_id' => Auth::id(),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'motivo' => $datos['motivo'],
                'estado' => Reserva::ESTADO_APROBADA,
            ]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'bloqueo_manual_calendario',
                modulo: 'Calendario',
                descripcion: "Registró un bloqueo manual para {$startTime->format('d/m/Y H:i')} a {$endTime->format('H:i')}.",
                nivel: 'importante',
                sujeto: $reserva,
                valoresNuevos: [
                    'reserva_id' => $reserva->id,
                    'start_time' => $startTime->toDateTimeString(),
                    'end_time' => $endTime->toDateTimeString(),
                    'motivo' => $datos['motivo'],
                    'estado' => Reserva::ESTADO_APROBADA,
                ],
            );

            return $reserva;
        });

        return back()->with(
            'success',
            'El bloqueo manual se registró correctamente en el calendario.'
        );
    }
}
