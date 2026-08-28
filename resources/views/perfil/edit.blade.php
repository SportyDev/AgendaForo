@extends('layouts.app')

@section('titulo', 'SRA - Mi Perfil')
@section('titulo_pagina', 'Mi Perfil')
@section('subtitulo_pagina', 'Administra tu información personal y la seguridad de tu cuenta.')

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
        <div
            id="toastSuccess"
            class="toast-notification fixed bottom-5 right-5 z-[9999] w-[calc(100%-2rem)] max-w-md"
            role="alert"
        >
            <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <span class="material-symbols-rounded">check_circle</span>
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-sm font-bold text-slate-900">Operación completada</p>
                    <p class="mt-1 text-sm leading-5 text-slate-500">{{ session('success') }}</p>
                </div>

                <button
                    type="button"
                    onclick="cerrarToast('toastSuccess')"
                    class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Cerrar"
                >
                    <span class="material-symbols-rounded text-[20px]">close</span>
                </button>
            </div>

            <div class="toast-progress bg-emerald-500"></div>
        </div>
    @endif

    @if($errors->any())
        <div
            id="toastError"
            class="toast-notification fixed bottom-5 right-5 z-[9999] w-[calc(100%-2rem)] max-w-md"
            role="alert"
        >
            <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-white p-4 shadow-2xl shadow-slate-900/15">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <span class="material-symbols-rounded">error</span>
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-sm font-bold text-slate-900">Revisa los datos</p>
                    <div class="mt-1 space-y-1">
                        @foreach($errors->all() as $error)
                            <p class="text-sm leading-5 text-slate-500">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>

                <button
                    type="button"
                    onclick="cerrarToast('toastError')"
                    class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Cerrar"
                >
                    <span class="material-symbols-rounded text-[20px]">close</span>
                </button>
            </div>

            <div class="toast-progress bg-red-500"></div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <span class="material-symbols-rounded">person</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Información Personal</h2>
                    <p class="text-sm text-slate-500">Actualiza los datos visibles de tu cuenta.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('perfil.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nombre</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $usuario->name) }}"
                        required
                        maxlength="255"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    >
                </div>

                <div>
                    <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-700">Teléfono</label>
                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        value="{{ old('telefono', $usuario->telefono) }}"
                        maxlength="30"
                        placeholder="Ej. 461 123 4567"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    >
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo Electrónico</label>
                    <input
                        type="email"
                        id="email"
                        value="{{ $usuario->email }}"
                        readonly
                        class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-500 shadow-sm"
                    >
                    <p class="mt-2 text-xs text-slate-400">El correo de acceso no puede modificarse desde este apartado.</p>
                </div>

                <div class="border-t border-slate-100 pt-5">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-500/20 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-2"
                    >
                        <span class="material-symbols-rounded text-[19px]">save</span>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                    <span class="material-symbols-rounded">lock</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Actualizar Contraseña</h2>
                    <p class="text-sm text-slate-500">Mantén segura tu cuenta cambiando tu contraseña.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña actual</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    >
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    >
                    <p class="mt-2 text-xs text-slate-400">Mínimo 8 caracteres.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar nueva contraseña</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    >
                </div>

                <div class="border-t border-slate-100 pt-5">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-500/20 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-2"
                    >
                        <span class="material-symbols-rounded text-[19px]">key</span>
                        Actualizar contraseña
                    </button>
                </div>
            </form>
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

                setTimeout(function () {
                    toast.remove();
                }, 300);
            };

            const toastSuccess = document.getElementById('toastSuccess');
            if (toastSuccess) {
                setTimeout(function () {
                    cerrarToast('toastSuccess');
                }, 4000);
            }

            const toastError = document.getElementById('toastError');
            if (toastError) {
                setTimeout(function () {
                    cerrarToast('toastError');
                }, 6000);
            }
        });
    </script>
@endsection
