@extends('layouts.app')

@section('titulo', 'SRA - Calendario de Eventos')
@section('titulo_pagina', 'Calendario de Eventos')
@section('subtitulo_pagina')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p>Visión global de ocupación y registro de bloqueos manuales.</p>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                <span class="h-2 w-2 rounded-full bg-blue-600"></span> Aprobada
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span> Pendiente
            </span>
        </div>
    </div>
@endsection

@section('contenido')

<style>
    #calendar { --agenda-line: #eef2f7; --agenda-text: #0f172a; }
    #calendar .fc { font-family: inherit; color: var(--agenda-text); }
    #calendar .fc-theme-standard .fc-scrollgrid { border: 0 !important; }
    #calendar .fc-theme-standard td, #calendar .fc-theme-standard th { border-color: var(--agenda-line) !important; }
    #calendar .fc-scrollgrid-section > * { border-left: 0 !important; border-right: 0 !important; }
    #calendar .fc-col-header-cell { background: #fff !important; }
    
    #calendar .fc-col-header-cell-cushion {
        display: block; padding: 0.85rem 0.5rem !important; color: #475569 !important;
        font-size: 0.72rem !important; font-weight: 700 !important; letter-spacing: 0.04em; text-transform: uppercase;
    }

    #calendar .fc-daygrid-day { background: #fff; }
    #calendar .fc-daygrid-day-frame { min-height: 7rem; }
    #calendar .fc-daygrid-day-number {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 2rem; height: 2rem; margin: 0.35rem; border-radius: 999px;
        color: #475569 !important; font-size: 0.8rem; font-weight: 700; text-decoration: none !important;
    }

    #calendar .fc-day-today .fc-daygrid-day-number { background: #0f172a; color: #fff !important; }
    #calendar .fc-day-today, #calendar .fc-day-past { background: #f8fafc !important; }
    #calendar .fc-day-past .fc-daygrid-day-number { color: #94a3b8 !important; }
    #calendar .fc-daygrid-day:hover { background: #fafafa; }

    #calendar .fc-timegrid-slot { height: 2.85rem; }
    #calendar .fc-timegrid-slot-label-cushion, #calendar .fc-timegrid-axis-cushion {
        padding: 0 0.65rem 0 0 !important; color: #94a3b8 !important; font-size: 0.7rem !important; font-weight: 600 !important;
    }
    #calendar .fc-timegrid-now-indicator-line { border-color: #2563eb !important; border-width: 2px !important; }
    #calendar .fc-timegrid-now-indicator-arrow { border-color: #2563eb !important; }

    #calendar .fc-event {
        border: 0 !important; border-radius: 0.65rem !important; padding: 0.25rem 0.35rem !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    #calendar .fc-event-title { font-size: 0.75rem !important; font-weight: 700 !important; line-height: 1.3 !important; }
    #calendar .fc-timegrid-event .fc-event-time { font-size: 0.7rem !important; opacity: 0.9; margin-bottom: 2px; }

    .time-option.selected { border-color: #0f172a !important; background-color: #0f172a !important; color: #ffffff !important; }
    .time-option:disabled { cursor: not-allowed !important; opacity: 0.3 !important; background-color: #f1f5f9 !important; color: #94a3b8 !important; }

    .agenda-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
    .agenda-switch { display: inline-flex; align-items: center; padding: 0.2rem; border: 1px solid #e2e8f0; border-radius: 0.85rem; background: #f8fafc; }
    .agenda-switch button { min-width: 5.7rem; border: 0; border-radius: 0.68rem; padding: 0.62rem 0.85rem; color: #64748b; background: transparent; font-size: 0.82rem; font-weight: 700; transition: all 160ms ease; }
    .agenda-switch button:hover { color: #0f172a; }
    .agenda-switch button.is-active { color: #0f172a; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08); }
    .agenda-date-nav { display: flex; align-items: center; justify-content: center; gap: 0.55rem; min-width: 0; }
    .agenda-nav-btn { display: inline-flex; align-items: center; justify-content: center; width: 2.35rem; height: 2.35rem; flex: 0 0 auto; border: 1px solid #e2e8f0; border-radius: 0.75rem; color: #475569; background: #fff; transition: all 160ms ease; }
    .agenda-nav-btn:hover { border-color: #cbd5e1; background: #f8fafc; color: #0f172a; }
    .agenda-current { min-width: 11rem; text-align: center; }
    .agenda-current-label { color: #0f172a; font-size: 0.95rem; font-weight: 800; line-height: 1.2; }
    .agenda-current-subtitle { margin-top: 0.18rem; color: #94a3b8; font-size: 0.72rem; font-weight: 600; }
    
    .btn-bloqueo { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background: #0f172a; color: white; padding: 0.62rem 1rem; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 700; transition: background 160ms ease; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
    .btn-bloqueo:hover { background: #1e293b; }

    .toast-notification { opacity: 0; transform: translateY(20px); animation: toastEntrada 0.35s ease-out forwards; }
    .toast-notification.saliendo { animation: toastSalida 0.3s ease-in forwards; }
    .toast-progress { height: 3px; width: 100%; margin-top: -3px; border-radius: 0 0 999px 999px; animation: toastTiempo linear forwards; }
    #toastSuccess .toast-progress { animation-duration: 4s; }
    #toastError .toast-progress { animation-duration: 6s; }

    @keyframes toastEntrada { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes toastSalida { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(20px); } }
    @keyframes toastTiempo { from { width: 100%; } to { width: 0%; } }

    @media (max-width: 768px) {
        .agenda-toolbar { justify-content: center; }
        .agenda-date-nav { order: 3; width: 100%; margin-top: 1rem; }
    }
</style>

@if(session('success'))
    <div id="toastSuccess" class="toast-notification fixed bottom-5 right-5 z-[9999] w-[calc(100%-2rem)] max-w-md" role="alert">
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <span class="material-symbols-rounded">check_circle</span>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <p class="text-sm font-bold text-slate-900">Calendario actualizado</p>
                <p class="mt-1 text-sm leading-5 text-slate-500">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="cerrarToast('toastSuccess')" class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <span class="material-symbols-rounded text-[20px]">close</span>
            </button>
        </div>
        <div class="toast-progress bg-emerald-500"></div>
    </div>
@endif

@if($errors->any())
    <div id="toastError" class="toast-notification fixed bottom-5 right-5 z-[9999] w-[calc(100%-2rem)] max-w-md" role="alert">
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <span class="material-symbols-rounded">error</span>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <p class="text-sm font-bold text-slate-900">No fue posible completar la solicitud</p>
                <div class="mt-1 space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="text-sm leading-5 text-slate-500">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            <button type="button" onclick="cerrarToast('toastError')" class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <span class="material-symbols-rounded text-[20px]">close</span>
            </button>
        </div>
        <div class="toast-progress bg-red-500"></div>
    </div>
@endif

<div class="overflow-hidden rounded-3xl border border-slate-100 bg-white p-4 shadow-xl shadow-slate-200/40 ring-1 ring-slate-900/5 sm:p-6">
    <div class="agenda-toolbar">
        <div class="agenda-switch" role="group">
            <button type="button" id="btnVistaMes" class="is-active">Mes</button>
            <button type="button" id="btnVistaSemana">Semana</button>
        </div>

        <div class="agenda-date-nav">
            <button type="button" id="btnAnterior" class="agenda-nav-btn"><span class="material-symbols-rounded text-[20px]">chevron_left</span></button>
            <div class="agenda-current">
                <div id="agendaCurrentLabel" class="agenda-current-label">Cargando…</div>
                <div id="agendaCurrentSubtitle" class="agenda-current-subtitle"></div>
            </div>
            <button type="button" id="btnSiguiente" class="agenda-nav-btn"><span class="material-symbols-rounded text-[20px]">chevron_right</span></button>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" id="btnHoy" class="agenda-nav-btn" style="width:auto; padding:0 1rem; font-weight:700; font-size:0.8rem;">Hoy</button>
        </div>
    </div>

    <div id="calendar"></div>
</div>

@endsection

@section('modales')

<div id="modalBloqueo" class="fixed inset-0 z-[100] hidden" aria-labelledby="modalBloqueoTitulo" role="dialog" aria-modal="true">
    <div id="modalBloqueoBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl">

            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                        <span class="material-symbols-rounded">block</span>
                    </div>
                    <div>
                        <h2 id="modalBloqueoTitulo" class="text-lg font-bold text-slate-900">Registrar Bloqueo Manual</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Impide nuevas solicitudes en un horario específico.</p>
                    </div>
                </div>
                <button type="button" id="btnCerrarModalBloqueo" class="rounded-xl p-2 text-slate-400 hover:bg-slate-200 hover:text-slate-700">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <form id="formBloqueo" method="POST" action="{{ route('admin.calendario.bloquear') }}" class="min-h-0 overflow-y-auto px-6 py-6">
                @csrf
                <div class="space-y-5">
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="nombre_evento" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Título del bloqueo</label>
                            <input type="text" id="nombre_evento" name="nombre_evento" maxlength="150" required placeholder="Ej. Mantenimiento, Evento Institucional..." value="{{ old('nombre_evento', 'Bloqueo Administrativo') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:ring-2 focus:ring-slate-900/20">
                        </div>

                        <div>
                            <label for="fecha_bloqueo" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Fecha</label>
                            <div class="relative">
                                <span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">calendar_month</span>
                                <input type="date" id="fecha_bloqueo" name="fecha" value="{{ old('fecha') }}" required readonly class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-500 focus:border-transparent focus:ring-0">
                            </div>
                        </div>

                        <div class="hidden sm:block"></div> <!-- Spacer -->

                        <div>
                            <label class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Hora de inicio</label>
                            <input type="hidden" id="start_time_bloqueo" name="start_time" value="{{ old('start_time') }}" required>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2">
                                    <span class="material-symbols-rounded text-[20px] text-slate-600">schedule</span>
                                    <span id="start_time_bloqueo_display" class="text-sm font-bold text-slate-800">Selecciona una hora</span>
                                </div>
                                <div class="grid max-h-44 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                    @for ($hora = 7; $hora <= 21; $hora++)
                                        @foreach ([0, 30] as $minuto)
                                            @php $valor = sprintf('%02d:%02d', $hora, $minuto); @endphp
                                            @if($valor !== '21:30')
                                                <button type="button" data-type="start" data-time="{{ $valor }}" class="time-option rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-200">
                                                    {{ $valor }}
                                                </button>
                                            @endif
                                        @endforeach
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Hora de término</label>
                            <input type="hidden" id="end_time_bloqueo" name="end_time" value="{{ old('end_time') }}" required>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2">
                                    <span class="material-symbols-rounded text-[20px] text-slate-600">schedule</span>
                                    <span id="end_time_bloqueo_display" class="text-sm font-bold text-slate-800">Selecciona una hora</span>
                                </div>
                                <div class="grid max-h-44 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                    @for ($hora = 7; $hora <= 22; $hora++)
                                        @foreach ([0, 30] as $minuto)
                                            @php $valor = sprintf('%02d:%02d', $hora, $minuto); @endphp
                                            @if($valor !== '07:00')
                                                <button type="button" data-type="end" data-time="{{ $valor }}" class="time-option rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-200">
                                                    {{ $valor }}
                                                </button>
                                            @endif
                                        @endforeach
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="motivo_bloqueo" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Motivo del bloqueo / Notas</label>
                        <textarea id="motivo_bloqueo" name="motivo" rows="2" maxlength="500" required placeholder="Describe brevemente por qué se bloquea este espacio..." class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/20">{{ old('motivo') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex shrink-0 flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" id="btnCancelarModalBloqueo" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-slate-900/30 hover:bg-slate-800">Registrar Bloqueo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const modalBloqueo = document.getElementById('modalBloqueo');
    const fechaInput = document.getElementById('fecha_bloqueo');
    const startInput = document.getElementById('start_time_bloqueo');
    const endInput = document.getElementById('end_time_bloqueo');
    const startDisplay = document.getElementById('start_time_bloqueo_display');
    const endDisplay = document.getElementById('end_time_bloqueo_display');

    let bloquesOcupados = [];
    let inicioSeleccionadoMins = null;
    const MINUTOS_LIMPIEZA = 60;
    const DURACION_MINIMA = 30;

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

    function actualizarBotones() {
        document.querySelectorAll('.time-option[data-type="start"]').forEach(btn => {
            const startMins = aMinutos(btn.dataset.time);
            const enMedioDeEvento = bloquesOcupados.some(b => startMins >= b.inicio && startMins < b.fin);
            let sinEspacioSuficiente = false;
            const siguienteBloque = bloquesOcupados.filter(b => b.inicio >= startMins).sort((a,b) => a.inicio - b.inicio)[0];
            
            if (siguienteBloque && (startMins + DURACION_MINIMA + MINUTOS_LIMPIEZA > siguienteBloque.inicio)) {
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

        document.querySelectorAll('.time-option[data-type="end"]').forEach(btn => {
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

    window.abrirModalBloqueo = function (fechaStr = null, startStr = null, endStr = null) {
        document.getElementById('formBloqueo').reset();
        startInput.value = '';
        endInput.value = '';
        inicioSeleccionadoMins = null;
        
        startDisplay.textContent = 'Selecciona una hora';
        endDisplay.textContent = 'Selecciona una hora';
        document.querySelectorAll('.time-option').forEach(btn => btn.classList.remove('selected'));

        let fechaUso = fechaStr;
        if (!fechaUso) {
            const tzoffset = (new Date()).getTimezoneOffset() * 60000;
            fechaUso = (new Date(Date.now() - tzoffset)).toISOString().split('T')[0];
        }
        fechaInput.value = fechaUso;

        bloquesOcupados = [];
        calendar.getEvents().forEach(evento => {
            if (!evento.start || !evento.end || evento.display === 'background') return;
            const evYear = evento.start.getFullYear();
            const evMonth = String(evento.start.getMonth() + 1).padStart(2, '0');
            const evDay = String(evento.start.getDate()).padStart(2, '0');
            if (`${evYear}-${evMonth}-${evDay}` === fechaUso) {
                bloquesOcupados.push({
                    inicio: (evento.start.getHours() * 60) + evento.start.getMinutes(),
                    fin: (evento.end.getHours() * 60) + evento.end.getMinutes() + MINUTOS_LIMPIEZA
                });
            }
        });

        actualizarBotones();

        if (startStr && endStr) {
            document.querySelector(`.time-option[data-type="start"][data-time="${startStr}"]`)?.click();
            document.querySelector(`.time-option[data-type="end"][data-time="${endStr}"]`)?.click();
        }

        modalBloqueo.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    document.querySelectorAll('.time-option').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;

            const tipo = this.dataset.type;
            const input = tipo === 'start' ? startInput : endInput;
            const display = tipo === 'start' ? startDisplay : endDisplay;

            input.value = this.dataset.time;
            display.textContent = this.dataset.time;

            document.querySelectorAll(`.time-option[data-type="${tipo}"]`).forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');

            if (tipo === 'start') {
                inicioSeleccionadoMins = aMinutos(this.dataset.time);
                if (endInput.value) {
                    const endMins = aMinutos(endInput.value);
                    const cruza = bloquesOcupados.some(b => b.inicio >= inicioSeleccionadoMins && b.inicio < endMins);
                    if (endMins <= inicioSeleccionadoMins || cruza) {
                        endInput.value = '';
                        endDisplay.textContent = 'Selecciona una hora';
                        document.querySelectorAll('.time-option[data-type="end"]').forEach(b => b.classList.remove('selected'));
                    }
                }
                actualizarBotones();
            }
        });
    });

    function cerrarModalBloqueo() {
        modalBloqueo.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('btnCerrarModalBloqueo').addEventListener('click', cerrarModalBloqueo);
    document.getElementById('btnCancelarModalBloqueo').addEventListener('click', cerrarModalBloqueo);
    document.getElementById('modalBloqueoBackdrop').addEventListener('click', cerrarModalBloqueo);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modalBloqueo.classList.contains('hidden')) cerrarModalBloqueo();
    });

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        firstDay: 1,
        selectable: true,
        selectMirror: true,
        headerToolbar: false,
        height: 'auto',
        slotMinTime: '07:00:00',
        slotMaxTime: '23:00:00',
        allDaySlot: false,
        displayEventTime: true,
        
        events: {
            url: @json(route('admin.calendario.eventos')),
            method: 'GET'
        },

        select: function (info) {
            calendar.unselect();
            if (calendar.view.type === 'dayGridMonth') {
                abrirModalBloqueo(info.startStr.split('T')[0]);
            } else {
                const startStr = info.start.toTimeString().substring(0, 5);
                const endStr = info.end.toTimeString().substring(0, 5);
                abrirModalBloqueo(info.startStr.split('T')[0], startStr, endStr);
            }
        },
        
        eventClick: function(info) {
            info.jsEvent.preventDefault();
        },

        datesSet: function () {
            const view = calendar.view;
            const start = view.currentStart;
            const end = view.currentEnd;
            
            if (view.type === 'dayGridMonth') {
                document.getElementById('agendaCurrentLabel').textContent = start.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
                document.getElementById('agendaCurrentSubtitle').textContent = '';
            } else {
                const semanaFin = new Date(end.getTime() - 86400000);
                document.getElementById('agendaCurrentLabel').textContent = `${start.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' })} – ${semanaFin.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' })}`;
                document.getElementById('agendaCurrentSubtitle').textContent = start.toLocaleDateString('es-MX', { year: 'numeric' });
            }
            
            document.getElementById('btnVistaMes').classList.toggle('is-active', view.type === 'dayGridMonth');
            document.getElementById('btnVistaSemana').classList.toggle('is-active', view.type === 'timeGridWeek');
        }
    });

    calendar.render();

    document.getElementById('btnVistaMes').addEventListener('click', () => calendar.changeView('dayGridMonth'));
    document.getElementById('btnVistaSemana').addEventListener('click', () => calendar.changeView('timeGridWeek'));
    document.getElementById('btnAnterior').addEventListener('click', () => calendar.prev());
    document.getElementById('btnSiguiente').addEventListener('click', () => calendar.next());
    document.getElementById('btnHoy').addEventListener('click', () => calendar.today());
});
</script>
@endsection