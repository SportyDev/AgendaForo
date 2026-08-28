@extends('layouts.app')

@section('titulo', 'SRA - Reservas')
@section('titulo_pagina', 'Panel de Reservas')
@section('subtitulo_pagina')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p>Selecciona una fecha disponible en el calendario para agendar el auditorio.</p>
        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
            <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                <span class="h-2 w-2 rounded-full bg-blue-600 shadow-sm shadow-blue-400"></span>
                Aprobada
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                <span class="h-2 w-2 rounded-full bg-amber-500 shadow-sm shadow-amber-400"></span>
                Solicitud pendiente
            </span>
        </div>
    </div>
@endsection

@section('contenido')
    <style>
        .fc .fc-button-primary {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            border-radius: 0.5rem !important;
            text-transform: capitalize !important;
            font-weight: 500 !important;
        }

        .fc .fc-button-primary:hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }

        .fc .fc-button-primary:disabled {
            opacity: 1 !important;
            cursor: default !important;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #f1f5f9 !important;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            text-transform: capitalize !important;
            margin: 0 !important;
            white-space: nowrap !important;
        }

        .fc .fc-toolbar-chunk:nth-child(2) {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.25rem !important;
            min-width: 0;
        }

        .fc .fc-prev-button,
        .fc .fc-next-button {
            width: 2.25rem !important;
            height: 2.25rem !important;
            min-width: 2.25rem !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .fc .fc-toolbar-title {
            margin-left: 0.1rem !important;
            margin-right: 0.1rem !important;
        }

        .fc .fc-day-today {
            background-color: #bfdbfe !important;
        }

        .time-option-start.selected,
        .time-option-end.selected {
            border-color: #2563eb !important;
            background-color: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.25);
        }

        .time-option-start:disabled,
        .time-option-end:disabled {
            cursor: not-allowed !important;
            opacity: 0.3 !important;
            background-color: #f1f5f9 !important;
            color: #94a3b8 !important;
        }

        .toast-notification {
            opacity: 0;
            transform: translateY(20px);
            animation: toastEntrada 0.35s ease-out forwards;
        }

        .toast-notification.saliendo {
            animation: toastSalida 0.3s ease-in forwards;
        }

        .toast-progress {
            height: 3px;
            width: 100%;
            margin-top: -3px;
            border-radius: 0 0 999px 999px;
            animation-name: toastTiempo;
            animation-timing-function: linear;
            animation-fill-mode: forwards;
        }

        #toastSuccess .toast-progress { animation-duration: 4s; }
        #toastError .toast-progress { animation-duration: 6s; }

        @keyframes toastEntrada {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes toastSalida {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(20px); }
        }

        @keyframes toastTiempo {
            from { width: 100%; }
            to { width: 0%; }
        }

        @media (max-width: 640px) {
            .fc .fc-header-toolbar {
                gap: 0.25rem !important;
            }

            .fc .fc-toolbar-title {
                font-size: 0.95rem !important;
                max-width: 130px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .toast-notification {
                left: 1rem !important;
                right: 1rem !important;
                bottom: 1rem !important;
                width: auto !important;
                max-width: none !important;
            }
        }
    </style>

    @if(session('success'))
        <div id="toastSuccess" class="toast-notification fixed bottom-5 right-5 z-[9999] w-[calc(100%-2rem)] max-w-md" role="alert">
            <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <span class="material-symbols-rounded">check_circle</span>
                </div>
                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-sm font-bold text-slate-900">Reserva actualizada</p>
                    <p class="mt-1 text-sm leading-5 text-slate-500">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="cerrarToast('toastSuccess')" class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Cerrar">
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
                <button type="button" onclick="cerrarToast('toastError')" class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Cerrar">
                    <span class="material-symbols-rounded text-[20px]">close</span>
                </button>
            </div>
            <div class="toast-progress bg-red-500"></div>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 shadow-xl shadow-slate-200/40 ring-1 ring-slate-900/5 sm:p-6">
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
                            <p class="mt-0.5 text-xs text-slate-500">Selecciona fecha, horario y necesidades del evento.</p>
                        </div>
                    </div>
                    <button type="button" id="btnCerrarModal" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" aria-label="Cerrar">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <form id="formReserva" method="POST" action="{{ route('solicitante.reservas.store') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">

                    <div>
                        <label for="fecha" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Fecha seleccionada</label>
                        <div class="relative">
                            <span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">calendar_month</span>
                            <input type="date" id="fecha" name="fecha" value="{{ old('fecha') }}" required readonly class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-500 focus:border-transparent focus:ring-0">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Hora de inicio</label>

                            <input type="hidden" id="start_time" name="start_time" value="{{ old('start_time') }}" required>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-3 flex items-center gap-2">
                                    <span class="material-symbols-rounded text-[20px] text-blue-600">schedule</span>
                                    <span id="start_time_display" class="text-sm font-bold text-slate-800">
                                        {{ old('start_time', 'Selecciona una hora') }}
                                    </span>
                                </div>

                                <div id="start_time_options" class="grid max-h-52 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                    @for ($hora = 7; $hora <= 21; $hora++)
                                        @foreach ([0, 30] as $minuto)
                                            @php
                                                $valor = sprintf('%02d:%02d', $hora, $minuto);
                                            @endphp

                                            @if($valor !== '21:30')
                                                <button
                                                    type="button"
                                                    data-time-start="{{ $valor }}"
                                                    class="time-option-start rounded-xl border border-slate-200 bg-white px-2 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"
                                                >
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

                            <input type="hidden" id="end_time" name="end_time" value="{{ old('end_time') }}" required>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="mb-3 flex items-center gap-2">
                                    <span class="material-symbols-rounded text-[20px] text-blue-600">schedule</span>
                                    <span id="end_time_display" class="text-sm font-bold text-slate-800">
                                        {{ old('end_time', 'Selecciona una hora') }}
                                    </span>
                                </div>

                                <div id="end_time_options" class="grid max-h-52 grid-cols-3 gap-2 overflow-y-auto pr-1">
                                    @for ($hora = 7; $hora <= 22; $hora++)
                                        @foreach ([0, 30] as $minuto)
                                            @php
                                                $valor = sprintf('%02d:%02d', $hora, $minuto);
                                            @endphp

                                            <button
                                                type="button"
                                                data-time-end="{{ $valor }}"
                                                class="time-option-end rounded-xl border border-slate-200 bg-white px-2 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"
                                            >
                                                {{ $valor }}
                                            </button>
                                        @endforeach
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="motivo" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Motivo del evento</label>
                        <textarea
                            id="motivo"
                            name="motivo"
                            rows="2"
                            maxlength="500"
                            required
                            placeholder="Ej. Conferencia de tecnologías emergentes..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        >{{ old('motivo') }}</textarea>
                        <div class="mt-1.5 flex justify-end">
                            <span class="text-xs text-slate-400">Máximo 500 caracteres</span>
                        </div>
                    </div>

                    <div>
                        <label for="necesidades" class="mb-2 block text-[13px] font-bold uppercase tracking-wide text-slate-700">Necesidades adicionales</label>
                        <textarea
                            id="necesidades"
                            name="necesidades"
                            rows="4"
                            maxlength="1000"
                            placeholder="Ej. 20 sillas, cafetera, proyector, micrófono, agua..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        >{{ old('necesidades') }}</textarea>
                        <div class="mt-1.5 flex items-center justify-between gap-3">
                            <span class="text-xs text-slate-400">Indica el equipo o mobiliario que necesitarás.</span>
                            <span class="shrink-0 text-xs text-slate-400">Máximo 1000</span>
                        </div>
                    </div>

                    </div>

                    <div class="shrink-0 border-t border-slate-100 bg-white px-6 py-4">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" id="btnCancelarModal" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-100">Cancelar</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-500/30 transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Enviar solicitud</button>
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
            const backdrop = document.getElementById('modalReservaBackdrop');
            const btnCerrar = document.getElementById('btnCerrarModal');
            const btnCancelar = document.getElementById('btnCancelarModal');
            const inputFecha = document.getElementById('fecha');

            const startInput = document.getElementById('start_time');
            const endInput = document.getElementById('end_time');

            const startDisplay = document.getElementById('start_time_display');
            const endDisplay = document.getElementById('end_time_display');

            const startButtons = document.querySelectorAll('.time-option-start');
            const endButtons = document.querySelectorAll('.time-option-end');

            function cerrarToast(id) {
                const toast = document.getElementById(id);

                if (!toast) {
                    return;
                }

                toast.classList.add('saliendo');

                window.setTimeout(() => {
                    toast.remove();
                }, 300);
            }

            window.cerrarToast = cerrarToast;

            const toastSuccess = document.getElementById('toastSuccess');
            const toastError = document.getElementById('toastError');

            if (toastSuccess) {
                window.setTimeout(() => cerrarToast('toastSuccess'), 4000);
            }

            if (toastError) {
                window.setTimeout(() => cerrarToast('toastError'), 6000);
            }

            function limpiarSeleccion(botones) {
                botones.forEach((button) => {
                    button.classList.remove('selected');
                });
            }

            function minutosDesdeHora(hora) {
                const [h, m] = hora.split(':').map(Number);
                return (h * 60) + m;
            }

            function actualizarHorariosFinales() {
                const inicio = startInput.value
                    ? minutosDesdeHora(startInput.value)
                    : null;

                endButtons.forEach((button) => {
                    const hora = minutosDesdeHora(button.dataset.timeEnd);

                    if (inicio !== null && hora <= inicio) {
                        button.disabled = true;
                    } else {
                        button.disabled = false;
                    }
                });

                if (
                    endInput.value &&
                    inicio !== null &&
                    minutosDesdeHora(endInput.value) <= inicio
                ) {
                    endInput.value = '';
                    endDisplay.textContent = 'Selecciona una hora';
                    limpiarSeleccion(endButtons);
                }
            }

            function seleccionarInicio(hora) {
                startInput.value = hora;
                startDisplay.textContent = hora;

                limpiarSeleccion(startButtons);

                const boton = document.querySelector(
                    `[data-time-start="${hora}"]`
                );

                if (boton) {
                    boton.classList.add('selected');
                }

                actualizarHorariosFinales();
            }

            function seleccionarFin(hora) {
                const inicio = startInput.value
                    ? minutosDesdeHora(startInput.value)
                    : null;

                const fin = minutosDesdeHora(hora);

                if (inicio !== null && fin <= inicio) {
                    return;
                }

                endInput.value = hora;
                endDisplay.textContent = hora;

                limpiarSeleccion(endButtons);

                const boton = document.querySelector(
                    `[data-time-end="${hora}"]`
                );

                if (boton) {
                    boton.classList.add('selected');
                }
            }

            startButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    seleccionarInicio(this.dataset.timeStart);
                });
            });

            endButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    if (!this.disabled) {
                        seleccionarFin(this.dataset.timeEnd);
                    }
                });
            });

            if (startInput.value) {
                seleccionarInicio(startInput.value);
            } else {
                actualizarHorariosFinales();
            }

            if (endInput.value) {
                seleccionarFin(endInput.value);
            }

            function abrirModal(fecha) {
                inputFecha.value = fecha;
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                setTimeout(() => {
                    document.getElementById('start_time_options')?.scrollIntoView({
                        block: 'nearest'
                    });
                }, 50);
            }

            function cerrarModal() {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            btnCerrar.addEventListener('click', cerrarModal);
            btnCancelar.addEventListener('click', cerrarModal);
            backdrop.addEventListener('click', cerrarModal);

            document.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Escape' &&
                    !modal.classList.contains('hidden')
                ) {
                    cerrarModal();
                }
            });

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                firstDay: 1,
                selectable: true,
                selectMirror: true,
                height: 'auto',
                headerToolbar: {
                    left: 'today',
                    center: 'prev title next',
                    right: 'dayGridMonth,timeGridWeek'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana'
                },
                events: {
                    url: @json(route('solicitante.reservas.eventos')),
                    method: 'GET',
                    failure: function () {
                        alert('No fue posible cargar las reservas del auditorio.');
                    }
                },
                select: function (info) {
                    calendar.unselect();
                    abrirModal(info.startStr);
                }
            });

            calendar.render();
        });
    </script>
@endsection
