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
    // Margen exclusivo después del evento (1 hora = 60 minutos)
    private const BUFFER_AFTER_MINUTES = 60;

    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(): View
    {
        return view('admin.calendario');
    }

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

        $eventos = [];

        foreach ($reservas as $reserva) {
            $estado = match ($reserva->estado) {
                Reserva::ESTADO_APROBADA => 'Aprobada',
                Reserva::ESTADO_PENDIENTE => 'Pendiente',
                default => 'Reserva',
            };

            $color = $reserva->estado === Reserva::ESTADO_APROBADA ? '#2563EB' : '#F59E0B';
            $start = $reserva->start_time;
            $end = $reserva->end_time;

            // Evento Principal (Formato Local sin la 'Z' de UTC)
            $eventos[] = [
                'id' => $reserva->id,
                'title' => $estado . ' - ' . ($reserva->nombre_evento ?: 'Bloqueo/Evento'),
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'color' => $color,
                'textColor' => '#FFFFFF',
            ];

            // Margen de Limpieza de 1 Hora visible en el calendario
            $bufferFin = $end->copy()->addMinutes(self::BUFFER_AFTER_MINUTES);
            $eventos[] = [
                'id' => 'buffer-after-' . $reserva->id,
                'title' => 'Margen / Limpieza',
                'start' => $end->format('Y-m-d\TH:i:s'),
                'end' => $bufferFin->format('Y-m-d\TH:i:s'),
                'display' => 'background',
                'backgroundColor' => '#cbd5e1',
                'borderColor' => '#cbd5e1',
            ];
        }

        return response()->json($eventos);
    }

    public function bloquear(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_evento' => ['required', 'string', 'max:150'],
            'fecha' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'motivo' => ['required', 'string', 'max:500'],
        ], [
            'nombre_evento.required' => 'Asigna un título al bloqueo.',
            'fecha.required' => 'Selecciona una fecha.',
            'start_time.required' => 'Selecciona la hora de inicio.',
            'end_time.required' => 'Selecciona la hora de término.',
            'end_time.after' => 'La hora de término debe ser posterior a la hora de inicio.',
            'motivo.required' => 'Escribe el motivo del bloqueo.',
        ]);

        $fecha = Carbon::parse($datos['fecha']);
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $fecha->format('Y-m-d') . ' ' . $datos['start_time']);
        $endTime = Carbon::createFromFormat('Y-m-d H:i', $fecha->format('Y-m-d') . ' ' . $datos['end_time']);

        // Se respeta la regla de limpieza del solicitante para detectar choques (+ 60 mins)
        $nuevoFinConLimpieza = $endTime->copy()->addMinutes(self::BUFFER_AFTER_MINUTES);

        $hayConflicto = Reserva::query()
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_APROBADA])
            ->where(function ($query) use ($startTime, $nuevoFinConLimpieza): void {
                $query
                    ->where('start_time', '<', $nuevoFinConLimpieza)
                    ->where('end_time', '>', $startTime->copy()->subMinutes(self::BUFFER_AFTER_MINUTES));
            })
            ->exists();

        if ($hayConflicto) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario seleccionado choca con un evento o su margen de limpieza.',
            ]);
        }

        $reserva = DB::transaction(function () use ($request, $datos, $startTime, $endTime): Reserva {
            $reserva = Reserva::create([
                'user_id' => Auth::id(),
                'nombre_evento' => $datos['nombre_evento'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'motivo' => $datos['motivo'],
                'estado' => Reserva::ESTADO_APROBADA,
            ]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'bloqueo_manual_calendario',
                modulo: 'Calendario',
                descripcion: "Registró un bloqueo manual para {$startTime->format('d/m/Y H:i')}.",
                nivel: 'importante',
                sujeto: $reserva,
            );

            return $reserva;
        });

        return back()->with('success', 'El bloqueo manual se registró correctamente en el calendario.');
    }
}
