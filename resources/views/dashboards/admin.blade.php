@extends('layouts.app')

@section('titulo', 'SRA - Bandeja de Solicitudes')
@section('titulo_pagina', 'Bandeja de Solicitudes')
@section('subtitulo_pagina', 'Revisa y gestiona las peticiones pendientes del auditorio.')

@section('contenido')

<style>
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

    #toastSuccess .toast-progress {
        animation-duration: 4s;
    }

    #toastError .toast-progress {
        animation-duration: 6s;
    }

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
                <p class="text-sm font-bold text-slate-900">Solicitud procesada</p>
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

<div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">

    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Solicitudes pendientes</h3>
                <p class="text-sm text-slate-500">Las peticiones se muestran en orden de fecha y hora.</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                {{ $reservas->total() }} pendientes
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Solicitante</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Fecha y Horario</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Motivo / Necesidades</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($reservas as $reserva)
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-6 py-5 align-top">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white">
                                    {{ strtoupper(substr($reserva->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $reserva->user?->name ?? 'Usuario no disponible' }}</p>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ $reserva->user?->email ?? 'Sin correo' }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-6 py-5 align-top">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $reserva->start_time?->translatedFormat('d \d\e F \d\e Y') }}
                            </p>
                            <div class="mt-1 flex items-center gap-2 text-sm text-slate-500">
                                <span class="material-symbols-rounded text-[18px]">schedule</span>
                                {{ $reserva->start_time?->format('H:i') }} - {{ $reserva->end_time?->format('H:i') }}
                            </div>
                        </td>

                        <td class="max-w-xl px-6 py-5 align-top">
                            <p class="text-sm font-semibold text-slate-800">
                                {{ \Illuminate\Support\Str::limit($reserva->motivo, 120) }}
                            </p>

                            @if($reserva->necesidades)
                                <div class="mt-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Necesidades</p>
                                    <p class="mt-1 text-sm leading-5 text-slate-600">
                                        {{ \Illuminate\Support\Str::limit($reserva->necesidades, 180) }}
                                    </p>
                                </div>
                            @else
                                <p class="mt-2 text-xs text-slate-400">Sin necesidades adicionales.</p>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-6 py-5 align-top text-right">
                            <div class="flex justify-end gap-2">
                                <form
                                    method="POST"
                                    action="{{ route('admin.solicitudes.aprobar', $reserva) }}"
                                    onsubmit="return confirm('¿Aprobar esta solicitud?');"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                                    >
                                        <span class="material-symbols-rounded text-[18px]">check</span>
                                        Aprobar
                                    </button>
                                </form>

                                <button
                                    type="button"
                                    onclick="abrirModalRechazo({{ $reserva->id }}, @js($reserva->user?->name ?? 'Solicitante'))"
                                    class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs font-bold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                                >
                                    <span class="material-symbols-rounded text-[18px]">close</span>
                                    Rechazar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                    <span class="material-symbols-rounded text-[34px]">task_alt</span>
                                </div>
                                <h3 class="mt-5 text-base font-bold text-slate-900">No hay solicitudes pendientes</h3>
                                <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                                    La bandeja está al día. No existen peticiones pendientes por revisar.
                                </p>
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

@endsection

@section('modales')

<div
    id="modalRechazo"
    class="fixed inset-0 z-[10000] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalRechazoTitulo"
>
    <div
        id="modalRechazoBackdrop"
        class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
    ></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <span class="material-symbols-rounded">rate_review</span>
                    </div>

                    <div>
                        <h2 id="modalRechazoTitulo" class="text-lg font-bold text-slate-900">
                            Rechazar solicitud
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Indica al solicitante por qué no fue aprobada.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    id="btnCerrarModalRechazo"
                    class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                    aria-label="Cerrar"
                >
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <form id="formRechazo" method="POST" class="px-6 py-6">
                @csrf
                @method('PUT')

                <div class="mb-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Solicitante</p>
                    <p id="nombreSolicitanteRechazo" class="mt-1 text-sm font-semibold text-slate-800"></p>
                </div>

                <div>
                    <label
                        for="nota_admin"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Motivo del rechazo
                    </label>

                    <textarea
                        id="nota_admin"
                        name="nota_admin"
                        rows="5"
                        maxlength="500"
                        required
                        placeholder="Escribe el motivo que verá el solicitante..."
                        class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                    ></textarea>

                    <div class="mt-2 flex justify-end">
                        <span class="text-xs text-slate-400">Máximo 500 caracteres</span>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        id="btnCancelarRechazo"
                        class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-100"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                    >
                        Rechazar solicitud
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalRechazo = document.getElementById('modalRechazo');
        const backdropRechazo = document.getElementById('modalRechazoBackdrop');
        const btnCerrar = document.getElementById('btnCerrarModalRechazo');
        const btnCancelar = document.getElementById('btnCancelarRechazo');
        const formRechazo = document.getElementById('formRechazo');
        const notaAdmin = document.getElementById('nota_admin');
        const nombreSolicitante = document.getElementById('nombreSolicitanteRechazo');

        window.cerrarToast = function (id) {
            const toast = document.getElementById(id);

            if (!toast) {
                return;
            }

            toast.classList.add('saliendo');

            setTimeout(function () {
                toast.remove();
            }, 300);
        };

        const toastSuccess = document.getElementById('toastSuccess');
        const toastError = document.getElementById('toastError');

        if (toastSuccess) {
            setTimeout(function () {
                cerrarToast('toastSuccess');
            }, 4000);
        }

        if (toastError) {
            setTimeout(function () {
                cerrarToast('toastError');
            }, 6000);
        }

        window.abrirModalRechazo = function (reservaId, solicitante) {
            formRechazo.action = @json(url('/admin/solicitudes')) + '/' + reservaId + '/rechazar';
            nombreSolicitante.textContent = solicitante;
            notaAdmin.value = '';

            modalRechazo.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            setTimeout(function () {
                notaAdmin.focus();
            }, 50);
        };

        function cerrarModalRechazo() {
            modalRechazo.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        btnCerrar.addEventListener('click', cerrarModalRechazo);
        btnCancelar.addEventListener('click', cerrarModalRechazo);
        backdropRechazo.addEventListener('click', cerrarModalRechazo);

        document.addEventListener('keydown', function (event) {
            if (
                event.key === 'Escape' &&
                !modalRechazo.classList.contains('hidden')
            ) {
                cerrarModalRechazo();
            }
        });
    });
</script>
@endsection
