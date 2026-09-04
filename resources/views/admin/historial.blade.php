@extends('layouts.app')

@section('titulo', 'SRA - Gestión Global')
@section('titulo_pagina', 'Gestión de Solicitudes')
@section('subtitulo_pagina', 'Audita, edita o cancela reservas y bloqueos de la plataforma.')

@section('contenido')
<style>
    .toast-notification { opacity: 0; transform: translateY(20px); animation: toastEntrada 0.35s ease-out forwards; }
    .toast-notification.saliendo { animation: toastSalida 0.3s ease-in forwards; }
    .toast-progress { height: 3px; width: 100%; margin-top: -3px; border-radius: 0 0 999px 999px; animation: toastTiempo linear forwards; }
    #toastSuccess .toast-progress { animation-duration: 4s; }
    #toastError .toast-progress { animation-duration: 6s; }
    @keyframes toastEntrada { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes toastSalida { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(20px); } }
    @keyframes toastTiempo { from { width: 100%; } to { width: 0%; } }

    .time-option.selected { border-color: #0f172a !important; background-color: #0f172a !important; color: #ffffff !important; }
    .time-option:disabled { cursor: not-allowed !important; opacity: 0.3 !important; background-color: #f1f5f9 !important; color: #94a3b8 !important; }
</style>

@if(session('success'))
    <div id="toastSuccess" class="toast-notification fixed bottom-5 right-5 z-[9999] w-[calc(100%-2rem)] max-w-md" role="alert">
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600"><span class="material-symbols-rounded">check_circle</span></div>
            <div class="min-w-0 flex-1 pt-0.5"><p class="text-sm font-bold text-slate-900">Operación exitosa</p><p class="mt-1 text-sm leading-5 text-slate-500">{{ session('success') }}</p></div>
            <button type="button" onclick="cerrarToast('toastSuccess')" class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"><span class="material-symbols-rounded text-[20px]">close</span></button>
        </div>
        <div class="toast-progress bg-emerald-500"></div>
    </div>
@endif

@if($errors->any())
    <div id="toastError" class="toast-notification fixed bottom-5 right-5 z-[9999] w-[calc(100%-2rem)] max-w-md" role="alert">
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600"><span class="material-symbols-rounded">error</span></div>
            <div class="min-w-0 flex-1 pt-0.5"><p class="text-sm font-bold text-slate-900">No fue posible guardar</p>
                <div class="mt-1 space-y-1">@foreach($errors->all() as $error)<p class="text-sm leading-5 text-slate-500">{{ $error }}</p>@endforeach</div>
            </div>
            <button type="button" onclick="cerrarToast('toastError')" class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"><span class="material-symbols-rounded text-[20px]">close</span></button>
        </div>
        <div class="toast-progress bg-red-500"></div>
    </div>
@endif

<div class="space-y-5">
    <!-- Pestañas de Navegación -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a href="{{ route('admin.historial.index', ['tab' => 'vigentes', 'search' => request('search'), 'estado' => request('estado')]) }}" 
               class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'vigentes' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                Eventos Vigentes y Pendientes
            </a>
            <a href="{{ route('admin.historial.index', ['tab' => 'pasados', 'search' => request('search'), 'estado' => request('estado')]) }}" 
               class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'pasados' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                Historial Pasado
            </a>
        </nav>
    </div>

    <!-- Panel de Búsqueda y Filtros -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <form method="GET" action="{{ route('admin.historial.index') }}" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <input type="hidden" name="tab" value="{{ $tab }}">
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
                <a href="{{ route('admin.historial.index', ['tab' => $tab]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">Limpiar</a>
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
                        @if($tab === 'vigentes')
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Acciones</th>
                        @endif
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

                            <!-- Acciones (Solo en Pestaña Vigentes) -->
                            @if($tab === 'vigentes')
                                <td class="whitespace-nowrap px-6 py-5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="abrirEditarReserva(@js($reserva->id), @js($reserva->start_time->format('Y-m-d')), @js($reserva->start_time->format('H:i')), @js($reserva->end_time->format('H:i')), @js($reserva->nombre_evento ?? ''), @js($reserva->motivo), @js($reserva->necesidades ?? ''))" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 focus:ring-2 focus:ring-slate-500/30" title="Editar Solicitud">
                                            <span class="material-symbols-rounded text-[18px]">edit</span> Editar
                                        </button>
                                        <form method="POST" action="{{ route('admin.historial.destroy', $reserva) }}" onsubmit="return confirm('¿Estás seguro de cancelar o eliminar permanentemente este evento?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 focus:ring-2 focus:ring-red-500/30" title="Eliminar/Cancelar">
                                                <span class="material-symbols-rounded text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tab === 'vigentes' ? 5 : 4 }}" class="px-6 py-16 text-center">
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

<!-- Modal Edición -->
<div id="modalEditarReserva" class="fixed inset-0 z-[10000] hidden" aria-labelledby="modalEditarReservaTitulo" role="dialog" aria-modal="true">
    <div id="modalEditarReservaBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                        <span class="material-symbols-rounded">edit_calendar</span>
                    </div>
                    <div>
                        <h2 id="modalEditarReservaTitulo" class="text-lg font-bold text-slate-900">Editar evento / solicitud</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Modifica administrativamente los detalles del evento.</p>
                    </div>
                </div>
                <button type="button" id="btnCerrarEditarReserva" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <form id="formEditarReserva" method="POST" class="min-h-0 overflow-y-auto px-6 py-6">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="edit_nombre_evento" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Nombre del Evento</label>
                            <input type="text" id="edit_nombre_evento" name="nombre_evento" maxlength="150" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/20">
                        </div>

                        <div>
                            <label for="edit_fecha" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Fecha</label>
                            <div class="relative">
                                <span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">calendar_month</span>
                                <input type="date" id="edit_fecha" name="fecha" required readonly class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-500 focus:border-transparent focus:ring-0">
                            </div>
                        </div>
                        <div class="hidden sm:block"></div>

                        <div>
                            <label class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Hora de inicio</label>
                            <input type="hidden" id="edit_start_time" name="start_time" required>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2">
                                    <span class="material-symbols-rounded text-[20px] text-slate-600">schedule</span>
                                    <span id="edit_start_time_display" class="text-sm font-bold text-slate-800">Selecciona hora</span>
                                </div>
                                <div class="grid max-h-44 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                    @for ($hora = 7; $hora <= 21; $hora++)
                                        @foreach ([0, 30] as $minuto)
                                            @php $valor = sprintf('%02d:%02d', $hora, $minuto); @endphp
                                            @if($valor !== '21:30')
                                                <button type="button" data-type="start" data-time="{{ $valor }}" class="time-option rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-200">{{ $valor }}</button>
                                            @endif
                                        @endforeach
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Hora de término</label>
                            <input type="hidden" id="edit_end_time" name="end_time" required>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2">
                                    <span class="material-symbols-rounded text-[20px] text-slate-600">schedule</span>
                                    <span id="edit_end_time_display" class="text-sm font-bold text-slate-800">Selecciona hora</span>
                                </div>
                                <div class="grid max-h-44 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                    @for ($hora = 7; $hora <= 22; $hora++)
                                        @foreach ([0, 30] as $minuto)
                                            @php $valor = sprintf('%02d:%02d', $hora, $minuto); @endphp
                                            @if($valor !== '07:00')
                                                <button type="button" data-type="end" data-time="{{ $valor }}" class="time-option rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-200">{{ $valor }}</button>
                                            @endif
                                        @endforeach
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="edit_motivo" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Motivo del evento</label>
                        <textarea id="edit_motivo" name="motivo" rows="2" maxlength="500" required class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/20"></textarea>
                    </div>

                    <div>
                        <label for="edit_necesidades" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Necesidades / Requerimientos</label>
                        <textarea id="edit_necesidades" name="necesidades" rows="2" maxlength="1000" class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/20"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex shrink-0 flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" id="btnCancelarEditarReserva" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-slate-900/30 hover:bg-slate-800">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEditar = document.getElementById('modalEditarReserva');
        const backdropEditar = document.getElementById('modalEditarReservaBackdrop');
        const formEditar = document.getElementById('formEditarReserva');
        const btnCerrarEditar = document.getElementById('btnCerrarEditarReserva');
        const btnCancelarEditar = document.getElementById('btnCancelarEditarReserva');
        
        const inputFecha = document.getElementById('edit_fecha');
        const inputInicio = document.getElementById('edit_start_time');
        const inputFin = document.getElementById('edit_end_time');
        const displayInicio = document.getElementById('edit_start_time_display');
        const displayFin = document.getElementById('edit_end_time_display');

        let bloquesOcupados = [];
        let inicioSeleccionadoMins = null;
        let reservaActualId = null;
        const MINUTOS_LIMPIEZA = 60;

        window.cerrarToast = function (id) {
            const toast = document.getElementById(id);
            if (toast) { toast.classList.add('saliendo'); setTimeout(() => toast.remove(), 300); }
        };
        if (document.getElementById('toastSuccess')) setTimeout(() => cerrarToast('toastSuccess'), 4000);
        if (document.getElementById('toastError')) setTimeout(() => cerrarToast('toastError'), 6000);

        function aMinutos(hora) {
            const [h, m] = hora.split(':').map(Number);
            return h * 60 + m;
        }

        async function cargarOcupacion(fechaStr) {
            bloquesOcupados = [];
            try {
                const response = await fetch("{{ route('admin.calendario.eventos') }}");
                const eventos = await response.json();
                
                eventos.forEach(evento => {
                    // Ignoramos el bloque visual de limpieza y el propio evento que estamos editando
                    if (evento.display === 'background' || evento.extendedProps?.reserva_id === reservaActualId) return;
                    
                    if (evento.start.split('T')[0] === fechaStr) {
                        const evtStart = new Date(evento.start);
                        const evtEnd = new Date(evento.end);
                        bloquesOcupados.push({
                            inicio: (evtStart.getHours() * 60) + evtStart.getMinutes(),
                            fin: (evtEnd.getHours() * 60) + evtEnd.getMinutes() + MINUTOS_LIMPIEZA
                        });
                    }
                });
                actualizarBotones();
            } catch (error) {
                console.error("Error al cargar eventos:", error);
            }
        }

        function actualizarBotones() {
            document.querySelectorAll('#modalEditarReserva .time-option[data-type="start"]').forEach(btn => {
                const startMins = aMinutos(btn.dataset.time);
                const enMedioDeEvento = bloquesOcupados.some(b => startMins >= b.inicio && startMins < b.fin);
                let sinEspacioSuficiente = false;
                const siguienteBloque = bloquesOcupados.filter(b => b.inicio >= startMins).sort((a,b) => a.inicio - b.inicio)[0];
                
                if (siguienteBloque && (startMins + 30 + MINUTOS_LIMPIEZA > siguienteBloque.inicio)) {
                    sinEspacioSuficiente = true;
                }

                if (enMedioDeEvento || sinEspacioSuficiente) {
                    btn.disabled = true;
                    btn.classList.add('opacity-40', 'cursor-not-allowed', 'bg-slate-100');
                } else {
                    btn.disabled = false;
                    btn.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-100');
                }
            });

            document.querySelectorAll('#modalEditarReserva .time-option[data-type="end"]').forEach(btn => {
                const endMins = aMinutos(btn.dataset.time);
                const enMedioDeEvento = bloquesOcupados.some(b => endMins > b.inicio && endMins <= b.fin);

                let bloqueadoPorCalculo = false;
                if (inicioSeleccionadoMins === null) {
                    bloqueadoPorCalculo = true;
                } else {
                    if (endMins <= inicioSeleccionadoMins) {
                        bloqueadoPorCalculo = true;
                    } else {
                        const siguienteBloque = bloquesOcupados.filter(b => b.inicio >= inicioSeleccionadoMins).sort((a,b) => a.inicio - b.inicio)[0];
                        if (siguienteBloque && (endMins + MINUTOS_LIMPIEZA > siguienteBloque.inicio)) {
                            bloqueadoPorCalculo = true;
                        }
                    }
                }

                if (enMedioDeEvento || bloqueadoPorCalculo) {
                    btn.disabled = true;
                    btn.classList.add('opacity-40', 'cursor-not-allowed');
                    if (enMedioDeEvento) btn.classList.add('bg-slate-100');
                } else {
                    btn.disabled = false;
                    btn.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-100');
                }
            });
        }

        window.abrirEditarReserva = function (id, fecha, inicio, fin, nombreEvento, motivo, necesidades) {
            formEditar.action = "{{ url('/admin/historial') }}/" + id;
            reservaActualId = id;
            
            document.querySelectorAll('#modalEditarReserva .time-option').forEach(btn => btn.classList.remove('selected'));
            
            inputFecha.value = fecha;
            inputInicio.value = inicio;
            inputFin.value = fin;
            displayInicio.textContent = inicio;
            displayFin.textContent = fin;
            inicioSeleccionadoMins = aMinutos(inicio);
            
            document.getElementById('edit_nombre_evento').value = nombreEvento || '';
            document.getElementById('edit_motivo').value = motivo || '';
            document.getElementById('edit_necesidades').value = necesidades || '';

            cargarOcupacion(fecha).then(() => {
                document.querySelector(`#modalEditarReserva .time-option[data-type="start"][data-time="${inicio}"]`)?.classList.add('selected');
                document.querySelector(`#modalEditarReserva .time-option[data-type="end"][data-time="${fin}"]`)?.classList.add('selected');
            });

            modalEditar.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        document.querySelectorAll('#modalEditarReserva .time-option').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.disabled) return;

                const tipo = this.dataset.type;
                const input = tipo === 'start' ? inputInicio : inputFin;
                const display = tipo === 'start' ? displayInicio : displayFin;

                input.value = this.dataset.time;
                display.textContent = this.dataset.time;

                document.querySelectorAll(`#modalEditarReserva .time-option[data-type="${tipo}"]`).forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');

                if (tipo === 'start') {
                    inicioSeleccionadoMins = aMinutos(this.dataset.time);
                    if (inputFin.value) {
                        const endMins = aMinutos(inputFin.value);
                        const cruza = bloquesOcupados.some(b => b.inicio >= inicioSeleccionadoMins && b.inicio < endMins);
                        if (endMins <= inicioSeleccionadoMins || cruza) {
                            inputFin.value = '';
                            displayFin.textContent = 'Selecciona hora';
                            document.querySelectorAll('#modalEditarReserva .time-option[data-type="end"]').forEach(b => b.classList.remove('selected'));
                        }
                    }
                    actualizarBotones();
                }
            });
        });

        function cerrarEditarReserva() {
            modalEditar.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        btnCerrarEditar.addEventListener('click', cerrarEditarReserva);
        btnCancelarEditar.addEventListener('click', cerrarEditarReserva);
        backdropEditar.addEventListener('click', cerrarEditarReserva);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modalEditar.classList.contains('hidden')) cerrarEditarReserva();
        });
    });
</script>
@endsection