<!DOCTYPE html>
<html lang="es" data-sidebar="open">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>@yield('titulo', 'AgendaForo')</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    />

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <script>
        const savedState = localStorage.getItem('sidebarState') || 'open';
        document.documentElement.setAttribute('data-sidebar', savedState);
    </script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2563EB",
                        "background-light": "#f4f7fe",
                        "surface-light": "#ffffff",
                        "text-main": "#0f172a",
                        "text-muted": "#64748b",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    }
                },
            },
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        :root {
            --sidebar-w: 280px;
            --sidebar-collapsed-w: 88px;
        }

        body:not(.preload) #sidebar,
        body:not(.preload) #main-content {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #sidebar {
            width: var(--sidebar-w);
        }

        #main-content {
            margin-left: var(--sidebar-w);
        }

        html[data-sidebar="closed"] #sidebar {
            width: var(--sidebar-collapsed-w);
        }

        html[data-sidebar="closed"] #main-content {
            margin-left: var(--sidebar-collapsed-w);
        }

        html[data-sidebar="closed"] .hide-on-collapse {
            opacity: 0;
            pointer-events: none;
            width: 0;
            overflow: hidden;
            display: none !important;
        }

        html[data-sidebar="closed"] .center-on-collapse {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        html[data-sidebar="closed"] .toggle-btn {
            position: static !important;
            margin: 0 auto !important;
            transform: rotate(180deg);
        }

        body.preload #sidebar-nav-scroll {
            visibility: hidden;
        }

        .material-symbols-rounded {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
        }

        .material-symbols-rounded.filled {
            font-variation-settings: 'FILL' 1;
        }
    </style>
</head>

<body class="preload bg-background-light text-text-main antialiased overflow-hidden">

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const sidebarScroll = document.getElementById('sidebar-nav-scroll');
            const savedScrollTop = localStorage.getItem('sraSidebarScrollTop');

            if (sidebarScroll && savedScrollTop !== null) {
                sidebarScroll.scrollTop = Number(savedScrollTop);
            }

            document.body.classList.remove('preload');
        });
    </script>

    <div
        id="mobile-overlay"
        onclick="toggleMobileSidebar()"
        class="fixed inset-0 z-40 hidden bg-slate-900/20 lg:hidden backdrop-blur-sm transition-opacity duration-300 opacity-0"
    ></div>

    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex h-full flex-col border-r border-slate-200 bg-white flex-shrink-0 overflow-x-hidden lg:translate-x-0 transform -translate-x-full lg:transition-none transition-transform duration-300 ease-in-out"
    >

        <div class="relative flex h-full flex-col p-5">

            <!-- Encabezado del sidebar -->
            <div class="relative mb-8 flex min-h-[40px] items-center justify-between center-on-collapse">

                <div class="flex items-center gap-3 pl-2 hide-on-collapse">

                    <!-- Ícono de Logo Modernizado con Gradiente -->
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white shadow-md shadow-blue-900/20">
                        <span class="material-symbols-rounded text-[24px] filled">
                            view_agenda
                        </span>
                    </div>

                    <div class="mt-1 flex flex-col whitespace-nowrap">
                        <h1 class="text-[20px] font-extrabold leading-none tracking-tight text-slate-900">
                            AgendaForo
                        </h1>

                        <p class="mt-1 text-[11px] font-medium uppercase tracking-widest text-slate-500">
                            Reservas de Auditorio
                        </p>
                    </div>

                </div>

                <button
                    type="button"
                    onclick="toggleDesktopSidebar()"
                    class="toggle-btn hidden rounded-lg p-1.5 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-800 lg:flex"
                    title="Expandir/Contraer"
                >
                    <span
                        class="material-symbols-rounded text-[20px]"
                        id="minimize-icon"
                    >
                        keyboard_double_arrow_left
                    </span>
                </button>

                <button
                    type="button"
                    onclick="toggleMobileSidebar()"
                    class="absolute right-0 z-10 p-1 text-slate-400 transition-colors hover:text-red-400 lg:hidden hide-on-collapse"
                >
                    <span class="material-symbols-rounded">
                        close
                    </span>
                </button>

            </div>


            <!-- Navegación -->
            <div
                id="sidebar-nav-scroll"
                class="flex-1 overflow-y-auto scrollbar-hide -mx-2 px-2"
            >

                @if(Auth::user() && Auth::user()->role === 'solicitante')

                    <!-- General -->
                    <div class="mb-3 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 hide-on-collapse">
                        General
                    </div>

                    <nav class="mb-8 flex flex-col gap-1.5">

                        <!-- Inicio (Historial de Reservas) -->
                        <!-- Inicio (Historial de Reservas) -->
                        <a
                            href="{{ route('inicio.index') }}"
                            class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all
                            {{ request()->routeIs('inicio.index', 'solicitante.reservas.*')
                                ? 'is-active bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white font-bold shadow-md shadow-blue-900/20'
                                : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span
                                class="material-symbols-rounded shrink-0 text-[22px]
                                {{ request()->routeIs('inicio.index', 'solicitante.reservas.*') ? 'text-white filled' : 'text-slate-400 group-hover:text-slate-700' }}"
                            >
                                space_dashboard
                            </span>

                            <span class="whitespace-nowrap hide-on-collapse">
                                Inicio
                            </span>
                        </a>

                        <!-- Calendario (Nueva Reserva) -->
                        <a
                            href="{{ route('solicitante.calendario.index') }}"
                            class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all
                            {{ request()->routeIs('solicitante.calendario.*')
                                ? 'is-active bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white font-bold shadow-md shadow-blue-900/20'
                                : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span
                                class="material-symbols-rounded shrink-0 text-[22px]
                                {{ request()->routeIs('solicitante.calendario.*') ? 'text-white filled' : 'text-slate-400 group-hover:text-slate-700' }}"
                            >
                                event_upcoming
                            </span>

                            <span class="whitespace-nowrap hide-on-collapse">
                                Nuevo Evento
                            </span>
                        </a>

                    </nav>

                    <!-- Cuenta -->
                    <div class="mb-3 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 hide-on-collapse">
                        Cuenta
                    </div>

                    <nav class="mb-8 flex flex-col gap-1.5">

                        <a
                            href="{{ route('perfil.edit') }}"
                            class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all
                            {{ request()->routeIs('perfil.*')
                                ? 'is-active bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white font-bold shadow-md shadow-blue-900/20'
                                : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span
                                class="material-symbols-rounded shrink-0 text-[22px]
                                {{ request()->routeIs('perfil.*') ? 'text-white filled' : 'text-slate-400 group-hover:text-slate-700' }}"
                            >
                                security
                            </span>

                            <span class="whitespace-nowrap hide-on-collapse">
                                Perfil y Seguridad
                            </span>
                        </a>

                    </nav>

                @endif

                @if(Auth::user() && Auth::user()->role === 'admin')

                    <div class="mb-3 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-400 hide-on-collapse">
                        Administración
                    </div>

                    <nav class="mb-8 flex flex-col gap-1.5">

                        <!-- Bandeja de solicitudes -->
                        <a
                            href="{{ route('admin.solicitudes.index') }}"
                            class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all
                            {{ request()->routeIs('admin.solicitudes.*')
                                ? 'is-active bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white font-bold shadow-md shadow-blue-900/20'
                                : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span
                                class="material-symbols-rounded shrink-0 text-[22px]
                                {{ request()->routeIs('admin.solicitudes.*') ? 'text-white filled' : 'text-slate-400 group-hover:text-slate-700' }}"
                            >
                                inbox
                            </span>

                            <span class="whitespace-nowrap hide-on-collapse">
                                Bandeja de solicitudes
                            </span>
                        </a>


                        <!-- Calendario Maestro -->
                        <a
                            href="{{ route('admin.calendario.index') }}"
                            class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all
                            {{ request()->routeIs('admin.calendario.*')
                                ? 'is-active bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white font-bold shadow-md shadow-blue-900/20'
                                : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span
                                class="material-symbols-rounded shrink-0 text-[22px]
                                {{ request()->routeIs('admin.calendario.*') ? 'text-white filled' : 'text-slate-400 group-hover:text-slate-700' }}"
                            >
                                calendar_month
                            </span>

                            <span class="whitespace-nowrap hide-on-collapse">
                                Calendario de Eventos
                            </span>
                        </a>


                        <!-- Gestión de Usuarios -->
                        <a
                            href="{{ route('admin.usuarios.index') }}"
                            class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all
                            {{ request()->routeIs('admin.usuarios.*')
                                ? 'is-active bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white font-bold shadow-md shadow-blue-900/20'
                                : 'font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span
                                class="material-symbols-rounded shrink-0 text-[22px]
                                {{ request()->routeIs('admin.usuarios.*') ? 'text-white filled' : 'text-slate-400 group-hover:text-slate-700' }}"
                            >
                                manage_accounts
                            </span>

                            <span class="whitespace-nowrap hide-on-collapse">
                                Gestión de Usuarios
                            </span>
                        </a>

                    </nav>

                @endif

            </div>


            <!-- Usuario -->
            <div class="relative mt-4 border-t border-slate-100 pt-4">

                <div class="relative flex items-center px-1 py-2 center-on-collapse">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 font-bold text-white shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1 overflow-hidden pl-3 pr-8 text-left hide-on-collapse">

                        <span class="block truncate text-[14px] font-bold text-slate-800">
                            {{ Auth::user()->name ?? 'Usuario' }}
                        </span>

                        <span class="mt-0.5 block truncate text-[11px] font-medium text-slate-400">
                            {{ Auth::user()->email ?? 'Sin correo' }}
                        </span>

                        <div class="mt-1.5">

                            @if(Auth::user() && Auth::user()->role === 'admin')

                                <span class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-indigo-600">
                                    Admin
                                </span>

                            @elseif(Auth::user() && Auth::user()->role === 'solicitante')

                                <span class="inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-600">
                                    Solicitante
                                </span>

                            @endif

                        </div>

                    </div>

                    <button
                        type="button"
                        onclick="abrirModalLogout()"
                        class="absolute right-0 z-10 shrink-0 rounded-xl bg-white p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500 hide-on-collapse"
                        title="Cerrar Sesión"
                    >
                        <span class="material-symbols-rounded text-[20px]">
                            logout
                        </span>
                    </button>

                </div>

            </div>

        </div>

    </aside>


    <!-- Contenido principal -->
    <div class="flex h-screen w-full flex-col">

        <main
            id="main-content"
            class="relative h-full flex-1 overflow-y-auto bg-background-light p-6 sm:p-10"
        >
            <button
                type="button"
                onclick="toggleMobileSidebar()"
                class="fixed left-4 top-4 z-30 flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-primary lg:hidden"
                aria-label="Abrir menú"
            >
                <span class="material-symbols-rounded text-[22px]">menu</span>
            </button>

            @yield('contenido')
        </main>

    </div>


    <!-- Modal de logout -->
    <div
        id="modalLogout"
        class="relative z-[100] hidden"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >

        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

                <div class="relative w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white text-left shadow-xl transition-all sm:my-8">

                    <div class="p-6">

                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600">
                            <span class="material-symbols-rounded text-[28px]">
                                logout
                            </span>
                        </div>

                        <div class="text-center">

                            <h3
                                class="text-xl font-bold text-slate-900"
                                id="modal-title"
                            >
                                ¿Cerrar Sesión?
                            </h3>

                            <p class="mt-2 text-[15px] leading-relaxed text-slate-500">
                                ¿Estás seguro de que deseas salir del sistema AgendaForo?
                            </p>

                        </div>

                    </div>


                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row-reverse">

                        <button
                            type="button"
                            id="btn-confirm-logout"
                            onclick="cerrarSesionSeguro(this)"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-[15px] font-semibold text-white shadow-sm transition-colors hover:bg-red-500 sm:w-auto"
                        >
                            Cerrar Sesión
                        </button>

                        <button
                            type="button"
                            onclick="cerrarModalLogout()"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[15px] font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 sm:w-auto"
                        >
                            Cancelar
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Formulario logout -->
    <form
        id="logout-form"
        method="POST"
        action="{{ route('logout') }}"
        style="display: none;"
    >
        @csrf
    </form>


    @yield('modales')
    @yield('scripts')


    <script>

        document.addEventListener('DOMContentLoaded', () => {

            const minIcon = document.getElementById('minimize-icon');

            const currentState =
                document.documentElement.getAttribute('data-sidebar');

            const sidebarScroll =
                document.getElementById('sidebar-nav-scroll');


            if (minIcon) {

                minIcon.innerText =
                    currentState === 'closed'
                        ? 'keyboard_double_arrow_right'
                        : 'keyboard_double_arrow_left';

            }


            protegerFormulariosContraDobleEnvio();


            if (sidebarScroll) {

                const guardarPosicionSidebar = () => {

                    localStorage.setItem(
                        'sraSidebarScrollTop',
                        String(sidebarScroll.scrollTop)
                    );

                };


                sidebarScroll.addEventListener(
                    'scroll',
                    guardarPosicionSidebar,
                    { passive: true }
                );


                sidebarScroll
                    .querySelectorAll('a.sidebar-link')
                    .forEach((link) => {

                        link.addEventListener(
                            'click',
                            guardarPosicionSidebar
                        );

                    });

            }

        });


        function protegerFormulariosContraDobleEnvio() {

            document.querySelectorAll('form').forEach((form) => {

                form.addEventListener('submit', (event) => {

                    if (form.dataset.enviando === 'true') {

                        event.preventDefault();
                        event.stopImmediatePropagation();

                        return;
                    }


                    if (!form.checkValidity()) {
                        return;
                    }


                    form.dataset.enviando = 'true';

                    form.setAttribute(
                        'aria-busy',
                        'true'
                    );


                    window.setTimeout(() => {

                        form.querySelectorAll(
                            'button[type="submit"], input[type="submit"]'
                        ).forEach((control) => {

                            control.disabled = true;

                            control.classList.add(
                                'cursor-wait',
                                'opacity-70'
                            );


                            if (control.tagName === 'BUTTON') {

                                control.dataset.textoOriginal =
                                    control.innerHTML;

                                control.innerHTML =
                                    '<span class="material-symbols-rounded animate-spin text-[18px]">sync</span> Procesando...';

                            }

                        });

                    }, 0);

                });

            });

        }


        function abrirModalLogout() {

            const modal =
                document.getElementById('modalLogout');

            if (modal) {
                modal.classList.remove('hidden');
            }

        }


        function cerrarModalLogout() {

            const modal =
                document.getElementById('modalLogout');

            if (modal) {
                modal.classList.add('hidden');
            }

        }


        function toggleDesktopSidebar() {

            const html =
                document.documentElement;

            const minIcon =
                document.getElementById('minimize-icon');


            const isClosed =
                html.getAttribute('data-sidebar') === 'closed';


            const newState =
                isClosed
                    ? 'open'
                    : 'closed';


            html.setAttribute(
                'data-sidebar',
                newState
            );


            localStorage.setItem(
                'sidebarState',
                newState
            );


            if (minIcon) {

                minIcon.innerText =
                    newState === 'closed'
                        ? 'keyboard_double_arrow_right'
                        : 'keyboard_double_arrow_left';

            }

        }


        function toggleMobileSidebar() {

            const sidebar =
                document.getElementById('sidebar');

            const overlay =
                document.getElementById('mobile-overlay');


            if (sidebar.classList.contains('-translate-x-full')) {

                sidebar.classList.remove(
                    '-translate-x-full'
                );

                overlay.classList.remove(
                    'hidden'
                );

                setTimeout(
                    () => overlay.classList.remove(
                        'opacity-0'
                    ),
                    10
                );

            } else {

                sidebar.classList.add(
                    '-translate-x-full'
                );

                overlay.classList.add(
                    'opacity-0'
                );

                setTimeout(
                    () => overlay.classList.add(
                        'hidden'
                    ),
                    300
                );

            }

        }


        function cerrarSesionSeguro(btn) {

            btn.disabled = true;

            btn.classList.add(
                'opacity-75',
                'cursor-wait'
            );


            btn.innerHTML =
                '<span class="material-symbols-rounded animate-spin text-[20px]">sync</span> Saliendo...';


            document
                .getElementById('logout-form')
                .submit();

        }

    </script>

</body>

</html>