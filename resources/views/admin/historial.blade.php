@extends('layouts.app')

@section('titulo', 'SRA - Historial Administrativo')
@section('titulo_pagina', 'Historial Administrativo')
@section('subtitulo_pagina', 'Auditoría completa de todas las solicitudes y eventos del auditorio.')

@section('contenido')
<div class="space-y-5">
    <!-- Panel de Búsqueda y Filtros -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <form method="GET" action="{{ route('admin.historial.index') }}" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Buscar reserva</label>
                    <div class="relative">
                        <span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">search</span>
                        <input name="search" value="{{ request('search') }}" type="search" placeholder="Nombre de evento, solicitante o motivo..." class="w-full rounded-xl border-slate-200 pl-10 text-sm focus:border-blue-600 focus:ring-blue-600">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-400">Estado</label>
                    <select name="estado" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-600 focus:ring-blue-600">
                        <option value="todos">Todos</option>
                        <option value="{{ \App\Models\Reserva::ESTADO_APROBADA }}" @selected(request('estado') == \App\Models\Reserva::ESTADO_APROBADA)>Aprobada / Bloqueo</option>
                        <option value="{{ \App\Models\Reserva::ESTADO_PENDIENTE }}" @selected(request('estado') == \App\Models\Reserva::ESTADO_PENDIENTE)>Pendientes</option>
                        <option value="{{ \App\Models\Reserva::ESTADO_RECHAZADA }}" @selected(request('estado') == \App\Models\Reserva::ESTADO_RECHAZADA)>Rechazadas</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.historial.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">Limpiar</a>
                <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-900/20 hover:from-slate-900 hover:to-blue-800">
                    <span class="material-symbols-rounded text-[19px]">filter_alt</span> Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla Historial -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Evento y Horario</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Solicitante</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Motivo y Detalles</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reservas as $reserva)
                        <tr class="transition hover:bg-slate-50/70">
                            <!-- Fecha y Horario -->
                            <td class="whitespace-nowrap px-6 py-5 align-top">
                                <p class="text-sm font-bold text-slate-900 max-w-[200px] truncate" title="{{ $reserva->nombre_evento }}">
                                    {{ $reserva->nombre_evento ?: 'Bloqueo / Evento sin título' }}
                                </p>
                                <div class="mt-1.5 flex flex-col gap-1 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="material-symbols-rounded text-[16px]">calendar_today</span>
                                        {{ $reserva->start_time?->translatedFormat('d M, Y') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                                        <span class="material-symbols-rounded text-[16px]">schedule</span>
                                        {{ $reserva->start_time?->format('H:i') }} - {{ $reserva->end_time?->format('H:i') }}
                                    </span>
                                </div>
                            </td>

                            <!-- Solicitante -->
                            <td class="px-6 py-5 align-top">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700">
                                        {{ strtoupper(substr($reserva->user?->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900">{{ $reserva->user?->name ?? 'Administración' }}</p>
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $reserva->user?->email ?? 'Sistema' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Motivo y Detalles -->
                            <td class="max-w-xs px-6 py-5 align-top">
                                <p class="text-sm text-slate-700 mb-2 truncate" title="{{ $reserva->motivo }}">
                                    {{ \Illuminate\Support\Str::limit($reserva->motivo, 80) }}
                                </p>
                                @if($reserva->estado === \App\Models\Reserva::ESTADO_RECHAZADA && $reserva->nota_admin)
                                    <div class="rounded-lg border border-red-100 bg-red-50 p-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-red-400">Motivo Rechazo</p>
                                        <p class="mt-0.5 text-xs text-red-700 truncate" title="{{ $reserva->nota_admin }}">
                                            {{ \Illuminate\Support\Str::limit($reserva->nota_admin, 60) }}
                                        </p>
                                    </div>
                                @elseif($reserva->necesidades)
                                    <p class="text-xs text-slate-500 truncate" title="{{ $reserva->necesidades }}"><span class="font-bold">Req:</span> {{ \Illuminate\Support\Str::limit($reserva->necesidades, 60) }}</p>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td class="whitespace-nowrap px-6 py-5 align-top">
                                @switch((int) $reserva->estado)
                                    @case(1)
                                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>Pendiente
                                        </span>
                                        @break
                                    @case(2)
                                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>Aprobada
                                        </span>
                                        @break
                                    @case(3)
                                        <span class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">
                                            <span class="h-2 w-2 rounded-full bg-red-500"></span>Rechazada
                                        </span>
                                        @break
                                @endswitch
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                                    <span class="material-symbols-rounded text-[30px]">history</span>
                                </div>
                                <h3 class="mt-4 text-sm font-bold text-slate-900">Historial vacío</h3>
                                <p class="mt-1 text-sm text-slate-500">No se encontraron registros con los filtros seleccionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reservas->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $reservas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection