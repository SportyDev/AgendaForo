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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservaController extends Controller
{
    private const BUFFER_MINUTES = 30;

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

        $eventos = [];

        foreach ($reservas as $reserva) {
            $esPendiente = $reserva->estado === Reserva::ESTADO_PENDIENTE;
            $color = $esPendiente ? '#F59E0B' : '#2563EB';
            $start = $reserva->start_time;
            $end = $reserva->end_time;

            $eventos[] = [
                'id' => 'reserva-' . $reserva->id,
                'title' => Str::words(trim($reserva->motivo ?? 'Reserva'), 8, '…'),
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
                'color' => $color,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'tipo' => $esPendiente ? 'pendiente' : 'aprobada',
                    'reserva_id' => $reserva->id,
                ],
            ];

            $bufferInicio = $start->copy()->subMinutes(self::BUFFER_MINUTES);
            $bufferFin = $end->copy()->addMinutes(self::BUFFER_MINUTES);

            $eventos[] = [
                'id' => 'buffer-before-' . $reserva->id,
                'title' => '',
                'start' => $bufferInicio->toIso8601String(),
                'end' => $start->toIso8601String(),
                'display' => 'background',
                'backgroundColor' => '#cbd5e1',
                'borderColor' => '#cbd5e1',
                'overlap' => false,
                'extendedProps' => [
                    'buffer' => true,
                    'reserva_id' => $reserva->id,
                ],
            ];

            $eventos[] = [
                'id' => 'buffer-after-' . $reserva->id,
                'title' => '',
                'start' => $end->toIso8601String(),
                'end' => $bufferFin->toIso8601String(),
                'display' => 'background',
                'backgroundColor' => '#cbd5e1',
                'borderColor' => '#cbd5e1',
                'overlap' => false,
                'extendedProps' => [
                    'buffer' => true,
                    'reserva_id' => $reserva->id,
                ],
            ];
        }

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

        if ($this->hayConflicto($startTime, $endTime)) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario seleccionado no está disponible. Debes dejar al menos 30 minutos de margen antes y después de cada reserva existente.',
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

        if ($this->hayConflicto($startTime, $endTime, $reserva->id)) {
            throw ValidationException::withMessages([
                'fecha' => 'El horario seleccionado no está disponible. Debes dejar al menos 30 minutos de margen antes y después de cada reserva existente.',
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

    private function hayConflicto(Carbon $startTime, Carbon $endTime, ?int $reservaId = null): bool
    {
        $startWithBuffer = $startTime->copy()->subMinutes(self::BUFFER_MINUTES);
        $endWithBuffer = $endTime->copy()->addMinutes(self::BUFFER_MINUTES);

        return Reserva::query()
            ->when($reservaId !== null, fn ($query) => $query->where('id', '!=', $reservaId))
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_APROBADA])
            ->where(function ($query) use ($startWithBuffer, $endWithBuffer) {
                $query
                    ->where('start_time', '<', $endWithBuffer)
                    ->where('end_time', '>', $startWithBuffer);
            })
            ->exists();
    }
}
