@extends('layouts.app')

@section('titulo', 'SRA - Mis Reservas')
@section('titulo_pagina', 'Mis Reservas')
@section('subtitulo_pagina', 'Consulta, edita o cancela tus solicitudes de reserva.')

@section('contenido')

    <style>
        .toast-notification {
            opacity: 0;
            transform: translateY(20px);
            animation: toastEntrada .35s ease-out forwards;
        }

        .toast-notification.saliendo {
            animation: toastSalida .3s ease-in forwards;
        }

        .toast-progress {
            height: 3px;
            width: 100%;
            margin-top: -3px;
            border-radius: 0 0 999px 999px;
            animation: toastTiempo linear forwards;
        }

        #toastSuccess .toast-progress {
            animation-duration: 4s;
        }

        #toastError .toast-progress {
            animation-duration: 6s;
        }

        @keyframes toastEntrada {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toastSalida {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(20px);
            }
        }

        @keyframes toastTiempo {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }

        @media (max-width: 640px) {
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
                    <p class="text-sm font-bold text-slate-900">Operación completada</p>
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

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Fecha del Evento</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Horario</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Motivo</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Estado</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($reservas as $reserva)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                        <span class="material-symbols-rounded text-[21px]">calendar_month</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $reserva->start_time->translatedFormat('d \\d\\e F \\d\\e Y') }}
                                    </p>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center gap-2 text-sm text-slate-700">
                                    <span class="material-symbols-rounded text-[19px] text-slate-400">schedule</span>
                                    <span>{{ $reserva->start_time->format('H:i') }} - {{ $reserva->end_time->format('H:i') }}</span>
                                </div>
                            </td>

                            <td class="max-w-xs px-6 py-5">
                                <p class="truncate text-sm font-medium text-slate-700" title="{{ $reserva->motivo }}">
                                    {{ \Illuminate\Support\Str::limit($reserva->motivo, 70) }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-6 py-5">
                                @switch((int) $reserva->estado)
                                    @case(1)
                                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                            Pendiente
                                        </span>
                                        @break
                                    @case(2)
                                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                                            Aprobada
                                        </span>
                                        @break
                                    @case(3)
                                        <span class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">
                                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                            Rechazada
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                            Desconocido
                                        </span>
                                @endswitch
                            </td>

                            <td class="whitespace-nowrap px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if((int) $reserva->estado === \App\Models\Reserva::ESTADO_PENDIENTE)
                                        <button type="button" onclick="abrirEditarReserva(@js($reserva->id), @js($reserva->start_time->format('Y-m-d')), @js($reserva->start_time->format('H:i')), @js($reserva->end_time->format('H:i')), @js($reserva->motivo))" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500/30" title="Editar solicitud">
                                            <span class="material-symbols-rounded text-[18px]">edit</span>
                                            Editar
                                        </button>

                                        <form method="POST" action="{{ route('solicitante.reservas.destroy', $reserva) }}" onsubmit="return confirmarCancelacion(this)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500/30" title="Cancelar solicitud">
                                                <span class="material-symbols-rounded text-[18px]">delete</span>
                                                Cancelar
                                            </button>
                                        </form>
                                    @elseif((int) $reserva->estado === \App\Models\Reserva::ESTADO_RECHAZADA)
                                        @if(!empty($reserva->nota_admin))
                                            <button type="button" onclick="mostrarNota(@js($reserva->nota_admin))" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500/30" title="Revisar nota">
                                                <span class="material-symbols-rounded text-[18px]">info</span>
                                                Revisar nota
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-400" title="Sin nota administrativa">
                                                <span class="material-symbols-rounded text-[18px]">info</span>
                                                Sin nota
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs font-medium text-slate-400">Sin acciones</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <span class="material-symbols-rounded text-[34px]">event_busy</span>
                                    </div>
                                    <h3 class="mt-5 text-base font-bold text-slate-900">No hay reservas todavía</h3>
                                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">Aún no tienes solicitudes de reserva registradas.</p>
                                </div>
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

    <div id="modalEditarReserva" class="fixed inset-0 z-[10000] hidden" aria-labelledby="modalEditarReservaTitulo" role="dialog" aria-modal="true">
        <div id="modalEditarReservaBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <span class="material-symbols-rounded">edit_calendar</span>
                        </div>
                        <div>
                            <h2 id="modalEditarReservaTitulo" class="text-lg font-bold text-slate-900">Editar solicitud</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Modifica los datos mientras la solicitud siga pendiente.</p>
                        </div>
                    </div>
                    <button type="button" id="btnCerrarEditarReserva" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" aria-label="Cerrar">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <form id="formEditarReserva" method="POST" class="space-y-5 px-6 py-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="edit_fecha" class="mb-2 block text-sm font-semibold text-slate-700">Fecha</label>
                        <input type="date" id="edit_fecha" name="fecha" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="edit_start_time" class="mb-2 block text-sm font-semibold text-slate-700">Hora de inicio</label>
                            <input type="time" id="edit_start_time" name="start_time" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div>
                            <label for="edit_end_time" class="mb-2 block text-sm font-semibold text-slate-700">Hora de término</label>
                            <input type="time" id="edit_end_time" name="end_time" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                    </div>

                    <div>
                        <label for="edit_motivo" class="mb-2 block text-sm font-semibold text-slate-700">Motivo del evento</label>
                        <textarea id="edit_motivo" name="motivo" rows="4" maxlength="500" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"></textarea>
                        <div class="mt-2 text-right text-xs text-slate-400">Máximo 500 caracteres</div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <button type="button" id="btnCancelarEditarReserva" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-100">Cancelar</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalNota" class="fixed inset-0 z-[10000] hidden" aria-labelledby="modalNotaTitulo" role="dialog" aria-modal="true">
        <div id="modalNotaBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                            <span class="material-symbols-rounded">info</span>
                        </div>
                        <div>
                            <h2 id="modalNotaTitulo" class="text-lg font-bold text-slate-900">Nota administrativa</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Información sobre el rechazo de tu solicitud.</p>
                        </div>
                    </div>
                    <button type="button" id="btnCerrarNota" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" aria-label="Cerrar">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="rounded-2xl border border-red-100 bg-red-50/70 p-4">
                        <p id="contenidoNota" class="text-sm leading-6 text-slate-700"></p>
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                    <button type="button" id="btnCerrarNotaInferior" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.cerrarToast = function (id) {
                const toast = document.getElementById(id);
                if (!toast) return;
                toast.classList.add('saliendo');
                setTimeout(() => toast.remove(), 300);
            };

            if (document.getElementById('toastSuccess')) {
                setTimeout(() => cerrarToast('toastSuccess'), 4000);
            }

            if (document.getElementById('toastError')) {
                setTimeout(() => cerrarToast('toastError'), 6000);
            }

            window.confirmarCancelacion = function () {
                return confirm('¿Estás seguro de que deseas cancelar esta solicitud de reserva?');
            };

            const modalEditar = document.getElementById('modalEditarReserva');
            const backdropEditar = document.getElementById('modalEditarReservaBackdrop');
            const formEditar = document.getElementById('formEditarReserva');
            const btnCerrarEditar = document.getElementById('btnCerrarEditarReserva');
            const btnCancelarEditar = document.getElementById('btnCancelarEditarReserva');

            window.abrirEditarReserva = function (id, fecha, inicio, fin, motivo) {
                formEditar.action = "{{ url('/solicitante/reservas') }}/" + id;
                document.getElementById('edit_fecha').value = fecha;
                document.getElementById('edit_start_time').value = inicio;
                document.getElementById('edit_end_time').value = fin;
                document.getElementById('edit_motivo').value = motivo;
                modalEditar.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                setTimeout(() => document.getElementById('edit_fecha').focus(), 50);
            };

            function cerrarEditarReserva() {
                modalEditar.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            btnCerrarEditar.addEventListener('click', cerrarEditarReserva);
            btnCancelarEditar.addEventListener('click', cerrarEditarReserva);
            backdropEditar.addEventListener('click', cerrarEditarReserva);

            const modalNota = document.getElementById('modalNota');
            const backdropNota = document.getElementById('modalNotaBackdrop');
            const btnCerrarNota = document.getElementById('btnCerrarNota');
            const btnCerrarNotaInferior = document.getElementById('btnCerrarNotaInferior');
            const contenidoNota = document.getElementById('contenidoNota');

            window.mostrarNota = function (nota) {
                contenidoNota.textContent = nota;
                modalNota.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            function cerrarModalNota() {
                modalNota.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            btnCerrarNota.addEventListener('click', cerrarModalNota);
            btnCerrarNotaInferior.addEventListener('click', cerrarModalNota);
            backdropNota.addEventListener('click', cerrarModalNota);

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                if (!modalEditar.classList.contains('hidden')) cerrarEditarReserva();
                if (!modalNota.classList.contains('hidden')) cerrarModalNota();
            });
        });
    </script>
@endsection
