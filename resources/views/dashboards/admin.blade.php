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

<!-- Panel Banner -->
<div class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 p-6 text-white shadow-xl shadow-slate-900/10 sm:p-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-3xl">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-blue-100">
                <span class="material-symbols-rounded text-[18px]">admin_panel_settings</span>
                Panel de Administración
            </div>

            <h2 class="text-xl font-bold leading-tight sm:text-2xl">
                Gestión de solicitudes y control del auditorio
            </h2>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                Revisa, aprueba o rechaza las peticiones pendientes y supervisa la agenda del Centro Nacional de Innovación Educativa y Desarrollo Docente.
            </p>
        </div>

        <a
            href="{{ route('admin.calendario.index') }}"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-900 shadow-lg transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-white/40"
        >
            <span class="material-symbols-rounded text-[20px]">calendar_month</span>
            Ver calendario general
        </a>
    </div>
</div>

<!-- Layout de Cuadrícula: Divide a partir de XL para evitar que la tabla se comprima en pantallas medianas -->
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    
    <!-- Columna Izquierda (Tabla de Solicitudes) -->
    <div class="min-w-0 xl:col-span-2">
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Solicitudes pendientes</h3>
                        <p class="text-sm text-slate-500">Peticiones esperando revisión administrativa.</p>
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
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Evento y Horario</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Detalles</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($reservas as $reserva)
                            <tr class="transition hover:bg-slate-50/70">
                                
                                <!-- Usuario -->
                                <td class="px-6 py-5 align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white">
                                            {{ strtoupper(substr($reserva->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900">{{ $reserva->user?->name ?? 'Usuario no disponible' }}</p>
                                            <p class="mt-1 truncate text-xs text-slate-500">{{ $reserva->user?->email ?? 'Sin correo' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Evento y Fechas Combinadas -->
                                <td class="px-6 py-5 align-top">
                                    <p class="text-sm font-bold text-slate-900 max-w-[200px] truncate" title="{{ $reserva->nombre_evento }}">
                                        {{ $reserva->nombre_evento ?: 'Evento sin título' }}
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

                                <!-- Detalles: Motivo y Necesidades -->
                                <td class="px-6 py-5 align-top max-w-[250px]">
                                    <p class="text-sm text-slate-700 mb-2 truncate" title="{{ $reserva->motivo }}">
                                        <span class="font-semibold">Motivo:</span> {{ \Illuminate\Support\Str::limit($reserva->motivo, 50) }}
                                    </p>

                                    @if($reserva->necesidades)
                                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-2">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Requerimientos</p>
                                            <p class="mt-0.5 text-xs text-slate-600 truncate" title="{{ $reserva->necesidades }}">
                                                {{ \Illuminate\Support\Str::limit($reserva->necesidades, 60) }}
                                            </p>
                                        </div>
                                    @else
                                        <p class="text-xs italic text-slate-400">Sin requerimientos extras</p>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="whitespace-nowrap px-6 py-5 align-top text-right">
                                    <div class="flex flex-col justify-end gap-2 sm:flex-row">
                                        <form method="POST" action="{{ route('admin.solicitudes.aprobar', $reserva) }}" onsubmit="return confirm('¿Aprobar esta solicitud?');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500/30">
                                                <span class="material-symbols-rounded text-[16px]">check</span> Aprobar
                                            </button>
                                        </form>

                                        <button type="button" onclick="abrirModalRechazo({{ $reserva->id }}, @js($reserva->user?->name ?? 'Solicitante'))" class="inline-flex w-full justify-center items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 focus:ring-2 focus:ring-red-500/30">
                                            <span class="material-symbols-rounded text-[16px]">close</span> Rechazar
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                        <span class="material-symbols-rounded text-[30px]">task_alt</span>
                                    </div>
                                    <h3 class="mt-4 text-sm font-bold text-slate-900">Bandeja al día</h3>
                                    <p class="mt-1 text-sm text-slate-500">No existen peticiones pendientes por revisar.</p>
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

    <!-- Columna Derecha (Próximos Eventos) -->
    <aside class="xl:col-span-1">
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Próximos Eventos</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Reservas aprobadas a futuro.</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <span class="material-symbols-rounded text-[20px]">event</span>
                    </span>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($proximosEventos as $evento)
                    @php
                        $hoy = \Carbon\Carbon::today();
                        $fechaEvento = $evento->start_time?->copy()->startOfDay();
                        $dias = $fechaEvento ? $hoy->diffInDays($fechaEvento) : null;
                        
                        $proximidad = match ($dias) {
                            0 => 'Hoy',
                            1 => 'Mañana',
                            default => "En {$dias} días",
                        };
                        
                        // Si el día ya pasó pero sigue contando por error horario
                        if ($fechaEvento < $hoy) $proximidad = 'En curso';
                    @endphp

                    <div class="px-5 py-4 transition hover:bg-slate-50">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-slate-50 border border-slate-100 text-slate-700">
                                <span class="text-[9px] font-bold uppercase leading-none text-slate-400 mb-0.5">
                                    {{ $evento->start_time?->translatedFormat('M') }}
                                </span>
                                <span class="text-sm font-extrabold leading-none text-slate-800">
                                    {{ $evento->start_time?->format('d') }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900" title="{{ $evento->nombre_evento }}">
                                    {{ $evento->nombre_evento ?: 'Evento sin título' }}
                                </p>
                                <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                    <span class="material-symbols-rounded text-[14px]">schedule</span>
                                    <span>{{ $evento->start_time?->format('H:i') }} - {{ $evento->end_time?->format('H:i') }}</span>
                                </div>
                            </div>

                            <span class="shrink-0 rounded-full border border-blue-200 bg-blue-50 px-2 py-1 text-[10px] font-bold tracking-wide text-blue-700">
                                {{ $proximidad }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                            <span class="material-symbols-rounded text-[26px]">event_busy</span>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-slate-900">Agenda libre</h4>
                        <p class="mt-1 text-xs text-slate-500">No hay reservas aprobadas programadas.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </aside>

</div>

@endsection

@section('modales')
<!-- MODAL DE RECHAZO (Sin cambios) -->
<div id="modalRechazo" class="fixed inset-0 z-[10000] hidden" role="dialog" aria-modal="true" aria-labelledby="modalRechazoTitulo">
    <div id="modalRechazoBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <span class="material-symbols-rounded">rate_review</span>
                    </div>
                    <div>
                        <h2 id="modalRechazoTitulo" class="text-lg font-bold text-slate-900">Rechazar solicitud</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Indica al solicitante por qué no fue aprobada.</p>
                    </div>
                </div>
                <button type="button" id="btnCerrarModalRechazo" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700" aria-label="Cerrar">
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
                    <label for="nota_admin" class="mb-2 block text-sm font-bold text-slate-700">Motivo del rechazo</label>
                    <textarea id="nota_admin" name="nota_admin" rows="4" maxlength="500" required placeholder="Escribe el motivo que verá el solicitante..." class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20"></textarea>
                    <div class="mt-2 flex justify-end">
                        <span class="text-xs text-slate-400">Máximo 500 caracteres</span>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" id="btnCancelarRechazo" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-100">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 focus:ring-2 focus:ring-red-500/30">Rechazar solicitud</button>
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
            if (!toast) return;
            toast.classList.add('saliendo');
            setTimeout(function () { toast.remove(); }, 300);
        };

        if (document.getElementById('toastSuccess')) {
            setTimeout(function () { cerrarToast('toastSuccess'); }, 4000);
        }

        if (document.getElementById('toastError')) {
            setTimeout(function () { cerrarToast('toastError'); }, 6000);
        }

        window.abrirModalRechazo = function (reservaId, solicitante) {
            formRechazo.action = @json(url('/admin/solicitudes')) + '/' + reservaId + '/rechazar';
            nombreSolicitante.textContent = solicitante;
            notaAdmin.value = '';

            modalRechazo.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            setTimeout(function () { notaAdmin.focus(); }, 50);
        };

        function cerrarModalRechazo() {
            modalRechazo.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        btnCerrar.addEventListener('click', cerrarModalRechazo);
        btnCancelar.addEventListener('click', cerrarModalRechazo);
        backdropRechazo.addEventListener('click', cerrarModalRechazo);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modalRechazo.classList.contains('hidden')) {
                cerrarModalRechazo();
            }
        });
    });
</script>
@endsection