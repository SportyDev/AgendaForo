@extends('layouts.app')

@section('titulo', 'SRA - Reservas')
@section('titulo_pagina', 'Panel de Reservas')

@section('subtitulo_pagina')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p>Selecciona un día o un horario para solicitar el auditorio.</p>

        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
            <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                Aprobada
            </span>

            <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                Solicitud pendiente
            </span>
        </div>
    </div>
@endsection

@section('contenido')
    <style>
        #calendar {
            --agenda-line: #eef2f7;
            --agenda-line-strong: #e2e8f0;
            --agenda-text: #0f172a;
            --agenda-muted: #64748b;
        }

        #calendar .fc {
            font-family: inherit;
            color: var(--agenda-text);
        }

        #calendar .fc-theme-standard .fc-scrollgrid { border: 0 !important; }
        #calendar .fc-theme-standard td, #calendar .fc-theme-standard th { border-color: var(--agenda-line) !important; }
        #calendar .fc-scrollgrid-section > * { border-left: 0 !important; border-right: 0 !important; }
        #calendar .fc-col-header-cell { background: #fff !important; }

        #calendar .fc-col-header-cell-cushion {
            display: block;
            padding: 0.85rem 0.5rem !important;
            color: #475569 !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        #calendar .fc-daygrid-day { background: #fff; }
        #calendar .fc-daygrid-day-frame { min-height: 7rem; }
        #calendar .fc-daygrid-day-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            margin: 0.35rem;
            border-radius: 999px;
            color: #475569 !important;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none !important;
        }

        #calendar .fc-day-today .fc-daygrid-day-number { background: #0f172a; color: #fff !important; }
        #calendar .fc-day-today { background: #f8fafc !important; }
        #calendar .fc-day-past { background: #f8fafc !important; }
        #calendar .fc-day-past .fc-daygrid-day-number { color: #94a3b8 !important; }
        #calendar .fc-daygrid-day:hover { background: #fafafa; }

        /* Agenda semanal */
        #calendar .fc-timegrid-slot { height: 2.85rem; }
        #calendar .fc-timegrid-slot-label-cushion, #calendar .fc-timegrid-axis-cushion {
            padding: 0 0.65rem 0 0 !important;
            color: #94a3b8 !important;
            font-size: 0.7rem !important;
            font-weight: 600 !important;
        }

        #calendar .fc-timegrid-now-indicator-line { border-color: #2563eb !important; border-width: 2px !important; }
        #calendar .fc-timegrid-now-indicator-arrow { border-color: #2563eb !important; }

        /* Eventos */
        #calendar .fc-event {
            border: 0 !important;
            border-radius: 0.65rem !important;
            padding: 0.25rem 0.35rem !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
        }
        #calendar .fc-event-title {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
        }
        #calendar .fc-timegrid-event .fc-event-time {
            font-size: 0.7rem !important;
            opacity: 0.9;
            margin-bottom: 2px;
        }

        /* Botones de selección de hora en modal */
        .time-option.selected {
            border-color: #2563eb !important;
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        /* Cabecera personalizada */
        .agenda-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .agenda-switch {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            background: #f8fafc;
        }
        .agenda-switch button {
            min-width: 5.7rem;
            border: 0;
            border-radius: 0.68rem;
            padding: 0.62rem 0.85rem;
            color: #64748b;
            background: transparent;
            font-size: 0.82rem;
            font-weight: 700;
            transition: all 160ms ease;
        }
        .agenda-switch button:hover { color: #0f172a; }
        .agenda-switch button.is-active {
            color: #0f172a;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .agenda-date-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-width: 0;
        }
        .agenda-nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.35rem;
            height: 2.35rem;
            flex: 0 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            color: #475569;
            background: #fff;
            transition: all 160ms ease;
        }
        .agenda-nav-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
        }
        .agenda-current { min-width: 11rem; text-align: center; }
        .agenda-current-label { color: #0f172a; font-size: 0.95rem; font-weight: 800; line-height: 1.2; }
        .agenda-current-subtitle { margin-top: 0.18rem; color: #94a3b8; font-size: 0.72rem; font-weight: 600; }
        .agenda-today-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.62rem 0.85rem;
            color: #334155;
            background: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            transition: all 160ms ease;
        }
        .agenda-today-btn:hover { border-color: #cbd5e1; background: #f8fafc; }
    </style>

    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white p-4 shadow-xl shadow-slate-200/40 ring-1 ring-slate-900/5 sm:p-6">
        <div class="agenda-toolbar">
            <div class="agenda-switch" role="group" aria-label="Vista del calendario">
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
            <button type="button" id="btnHoy" class="agenda-today-btn">Hoy</button>
        </div>

        <div id="calendar"></div>
    </div>
@endsection

@section('modales')
    <div id="modalReserva" class="fixed inset-0 z-[100] hidden" aria-labelledby="modalReservaTitulo" role="dialog" aria-modal="true">
        <div id="modalReservaBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

        <div class="relative flex min-h-full items-center justify-center overflow-y-auto p-4">
            <div class="my-4 flex max-h-[calc(100vh-2rem)] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl sm:my-8">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <span class="material-symbols-rounded">event_note</span>
                        </div>
                        <div>
                            <h2 id="modalReservaTitulo" class="text-lg font-bold text-slate-900">Nueva Solicitud</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Selecciona la fecha, horario y detalles del evento.</p>
                        </div>
                    </div>
                    <button type="button" id="btnCerrarModal" class="rounded-xl p-2 text-slate-400 hover:bg-slate-200">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <!-- Formulario -->
                <form id="formReserva" method="POST" action="{{ route('solicitante.reservas.store') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Fecha seleccionada</label>
                            <div class="relative">
                                <span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">calendar_month</span>
                                <input type="date" id="fecha" name="fecha" value="{{ old('fecha') }}" required readonly class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-500 focus:border-transparent focus:ring-0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Selector de Hora Inicio -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Hora de inicio</label>
                                <input type="hidden" id="start_time" name="start_time" value="{{ old('start_time') }}" required>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2">
                                        <span class="material-symbols-rounded text-[20px] text-blue-600">schedule</span>
                                        <span id="start_time_display" class="text-sm font-bold text-slate-800">Selecciona una hora</span>
                                    </div>

                                    <div class="grid max-h-44 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                        @for ($hora = 7; $hora <= 21; $hora++)
                                            @foreach ([0, 30] as $minuto)
                                                @php $valor = sprintf('%02d:%02d', $hora, $minuto); @endphp
                                                @if($valor !== '21:30')
                                                    <button type="button" data-type="start" data-time="{{ $valor }}" class="time-option rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50">
                                                        {{ $valor }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <!-- Selector de Hora Fin -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Hora de término</label>
                                <input type="hidden" id="end_time" name="end_time" value="{{ old('end_time') }}" required>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2">
                                        <span class="material-symbols-rounded text-[20px] text-blue-600">schedule</span>
                                        <span id="end_time_display" class="text-sm font-bold text-slate-800">Selecciona una hora</span>
                                    </div>

                                    <div class="grid max-h-44 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                        @for ($hora = 7; $hora <= 22; $hora++)
                                            @foreach ([0, 30] as $minuto)
                                                @php $valor = sprintf('%02d:%02d', $hora, $minuto); @endphp
                                                <button type="button" data-type="end" data-time="{{ $valor }}" class="time-option rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50">
                                                    {{ $valor }}
                                                </button>
                                            @endforeach
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="nombre_evento" class="mb-2 block text-sm font-medium text-slate-700">Nombre del evento</label>
                            <input type="text" id="nombre_evento" name="nombre_evento" maxlength="150" required placeholder="Ej. Presentación de Proyectos" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" value="{{ old('nombre_evento') }}">
                        </div>

                        <div>
                            <label for="motivo" class="mb-2 block text-sm font-medium text-slate-700">Motivo del evento</label>
                            <textarea id="motivo" name="motivo" rows="2" maxlength="500" required placeholder="Explica brevemente la razón de la solicitud..." class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">{{ old('motivo') }}</textarea>
                        </div>

                        <div>
                            <label for="necesidades" class="mb-2 block text-sm font-medium text-slate-700">Necesidades adicionales</label>
                            <textarea id="necesidades" name="necesidades" rows="2" maxlength="1000" placeholder="Ej. 20 sillas, proyector, micrófono..." class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">{{ old('necesidades') }}</textarea>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-slate-100 bg-white px-6 py-4">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" id="btnCancelarModal" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Cancelar</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Enviar solicitud</button>
                        </div>
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
            const modal = document.getElementById('modalReserva');
            const inputFecha = document.getElementById('fecha');
            const inputInicio = document.getElementById('start_time');
            const inputFin = document.getElementById('end_time');
            const displayInicio = document.getElementById('start_time_display');
            const displayFin = document.getElementById('end_time_display');

            let bloquesOcupados = [];
            let inicioSeleccionadoMins = null;
            const MINUTOS_LIMPIEZA = 60;
            const DURACION_MINIMA = 30;

            function aMinutos(hora) {
                const [h, m] = hora.split(':').map(Number);
                return h * 60 + m;
            }

            function actualizarBotones() {
                // 1. Botones de Inicio
                document.querySelectorAll('.time-option[data-type="start"]').forEach(btn => {
                    const startMins = aMinutos(btn.dataset.time);
                    
                    // ¿Cae dentro de un evento existente (incluyendo su limpieza)?
                    const enMedioDeEvento = bloquesOcupados.some(b => startMins >= b.inicio && startMins < b.fin);
                    
                    // ¿Tiene espacio para el evento mínimo (30 min) + limpieza (60 min) antes del siguiente bloqueo?
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

                // 2. Botones de Fin
                document.querySelectorAll('.time-option[data-type="end"]').forEach(btn => {
                    const endMins = aMinutos(btn.dataset.time);
                    const enMedioDeEvento = bloquesOcupados.some(b => endMins > b.inicio && endMins <= b.fin);

                    let bloqueadoPorCalculo = false;
                    if (inicioSeleccionadoMins !== null) {
                        // El fin no puede ser menor o igual al inicio
                        if (endMins <= inicioSeleccionadoMins) {
                            bloqueadoPorCalculo = true;
                        } else {
                            // La hora de fin + limpieza ¿choca con el siguiente evento?
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

            function abrirModal(fechaStr) {
                // Parsear fecha exacta localmente para evitar desfases de zona horaria
                const [year, month, day] = fechaStr.split('-');
                const fechaDate = new Date(year, month - 1, day, 0, 0, 0);
                const hoy = new Date();
                hoy.setHours(0,0,0,0);
                
                if (fechaDate < hoy) return;

                // Limpieza total de formulario e interfaz
                document.getElementById('formReserva').reset();
                inputInicio.value = '';
                inputFin.value = '';
                inicioSeleccionadoMins = null;
                
                inputFecha.value = fechaStr;
                displayInicio.textContent = 'Selecciona una hora';
                displayFin.textContent = 'Selecciona una hora';

                document.querySelectorAll('.time-option').forEach(btn => btn.classList.remove('selected'));

                // Calcular bloques del día con el margen de 60 mins incluido
                bloquesOcupados = [];
                calendario.getEvents().forEach(evento => {
                    if (!evento.start || !evento.end || evento.display === 'background') return;
                    
                    const evYear = evento.start.getFullYear();
                    const evMonth = String(evento.start.getMonth() + 1).padStart(2, '0');
                    const evDay = String(evento.start.getDate()).padStart(2, '0');
                    const eventoFechaStr = `${evYear}-${evMonth}-${evDay}`;

                    if (eventoFechaStr === fechaStr) {
                        bloquesOcupados.push({
                            inicio: (evento.start.getHours() * 60) + evento.start.getMinutes(),
                            fin: (evento.end.getHours() * 60) + evento.end.getMinutes() + MINUTOS_LIMPIEZA
                        });
                    }
                });

                actualizarBotones();

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            document.querySelectorAll('.time-option').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.disabled) return;

                    const tipo = this.dataset.type;
                    const input = tipo === 'start' ? inputInicio : inputFin;
                    const display = tipo === 'start' ? displayInicio : displayFin;

                    input.value = this.dataset.time;
                    display.textContent = this.dataset.time;

                    document.querySelectorAll(`.time-option[data-type="${tipo}"]`).forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');

                    if (tipo === 'start') {
                        inicioSeleccionadoMins = aMinutos(this.dataset.time);
                        
                        // Si ya hay un fin seleccionado, validar si sigue siendo válido con el nuevo inicio
                        if (inputFin.value) {
                            const endMins = aMinutos(inputFin.value);
                            const cruza = bloquesOcupados.some(b => b.inicio >= inicioSeleccionadoMins && b.inicio < endMins);
                            
                            if (endMins <= inicioSeleccionadoMins || cruza) {
                                inputFin.value = '';
                                displayFin.textContent = 'Selecciona una hora';
                                document.querySelectorAll('.time-option[data-type="end"]').forEach(b => b.classList.remove('selected'));
                            }
                        }
                        
                        // Actualizar qué botones de fin están disponibles
                        actualizarBotones();
                    }
                });
            });

            document.getElementById('btnCerrarModal').addEventListener('click', () => { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); });
            document.getElementById('btnCancelarModal').addEventListener('click', () => { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); });

            // Configuración del Calendario
            const calendario = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                firstDay: 1,
                headerToolbar: false,
                height: 'auto',
                slotMinTime: '07:00:00',
                slotMaxTime: '23:00:00',
                allDaySlot: false,
                displayEventTime: true,
                
                events: {
                    url: @json(route('solicitante.reservas.eventos')),
                    method: 'GET'
                },

                dateClick: function (info) {
                    const fechaStr = info.dateStr.split('T')[0];
                    abrirModal(fechaStr);
                },
                
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                },

                datesSet: function () {
                    const view = calendario.view;
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

            calendario.render();

            document.getElementById('btnVistaMes').addEventListener('click', () => calendario.changeView('dayGridMonth'));
            document.getElementById('btnVistaSemana').addEventListener('click', () => calendario.changeView('timeGridWeek'));
            document.getElementById('btnAnterior').addEventListener('click', () => calendario.prev());
            document.getElementById('btnSiguiente').addEventListener('click', () => calendario.next());
            document.getElementById('btnHoy').addEventListener('click', () => calendario.today());
        });
    </script>
@endsection