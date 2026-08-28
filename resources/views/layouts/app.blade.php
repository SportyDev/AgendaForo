<!DOCTYPE html>
<html lang="es" data-sidebar="open">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('titulo', 'SRA')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
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
                    "active-bg": "#2563EB",
                    "active-text": "#ffffff",
                },
                fontFamily: {
                    "display": ["Inter", "sans-serif"]
                }
            },
        },
    }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        :root {
            --sidebar-w: 280px;
            --sidebar-collapsed-w: 88px;
        }

        body:not(.preload) #sidebar, 
        body:not(.preload) #main-content, 
        body:not(.preload) #top-header {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #sidebar { width: var(--sidebar-w); }
        #main-content { margin-left: var(--sidebar-w); }
        #top-header { left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); }

        html[data-sidebar="closed"] #sidebar { width: var(--sidebar-collapsed-w); }
        html[data-sidebar="closed"] #main-content { margin-left: var(--sidebar-collapsed-w); }
        html[data-sidebar="closed"] #top-header { left: var(--sidebar-collapsed-w); width: calc(100% - var(--sidebar-collapsed-w)); }

        html[data-sidebar="closed"] .hide-on-collapse { opacity: 0; pointer-events: none; width: 0; overflow: hidden; display: none !important; }
        html[data-sidebar="closed"] .center-on-collapse { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; }
        html[data-sidebar="closed"] .toggle-btn { position: static !important; margin: 0 auto !important; transform: rotate(180deg); }
        
        body.preload #sidebar-nav-scroll { visibility: hidden; }

        .material-symbols-rounded { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-rounded.filled { font-variation-settings: 'FILL' 1; }
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

    <div id="mobile-overlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-slate-900/20 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex h-full flex-col border-r border-slate-200 bg-white flex-shrink-0 overflow-x-hidden lg:translate-x-0 transform -translate-x-full lg:transition-none transition-transform duration-300 ease-in-out">
        
        <div class="flex h-full flex-col p-5 relative">
            
            <div class="flex items-center justify-between mb-8 relative center-on-collapse min-h-[40px]">
                <div class="flex items-center gap-3 hide-on-collapse pl-2">
                    <div class="text-primary flex items-center justify-center">
                        <span class="material-symbols-rounded text-[32px] filled">calendar_month</span>
                    </div>
                    <div class="flex flex-col whitespace-nowrap mt-1">
                        <h1 class="text-[20px] font-extrabold leading-none text-slate-900 tracking-tight">SRA</h1>
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-widest mt-1">Reservas de Auditorio</p>
                    </div>
                </div>

                <button type="button" onclick="toggleDesktopSidebar()" class="hidden lg:flex text-slate-400 hover:text-slate-800 transition-all p-1.5 rounded-lg hover:bg-slate-100 z-10 toggle-btn" title="Expandir/Contraer">
                    <span class="material-symbols-rounded text-[20px]" id="minimize-icon">keyboard_double_arrow_left</span>
                </button>
                
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden absolute right-0 text-slate-400 hover:text-red-400 transition-colors p-1 z-10 hide-on-collapse">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div id="sidebar-nav-scroll" class="flex-1 overflow-y-auto scrollbar-hide -mx-2 px-2">

                {{-- GENERAL --}}
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3 px-4 hide-on-collapse">General</div>
                <nav class="flex flex-col gap-1.5 mb-8">
                    <a href="{{ route('inicio.index') }}"
                       class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all {{ request()->routeIs('inicio.index') ? 'is-active bg-active-bg text-active-text font-bold shadow-sm' : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                        <span class="material-symbols-rounded shrink-0 text-[22px] {{ request()->routeIs('inicio.index') ? 'filled' : 'group-hover:text-slate-700' }}">grid_view</span>
                        <span class="hide-on-collapse whitespace-nowrap">Inicio</span>
                    </a>
                </nav>

                @if(Auth::user() && Auth::user()->role === 'admin')
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3 px-4 hide-on-collapse">Administración</div>
                    <nav class="flex flex-col gap-1.5 mb-8">
                        <a href="#"
                           class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all {{ request()->routeIs('admin.solicitudes.*') ? 'is-active bg-active-bg text-active-text font-bold shadow-sm' : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="material-symbols-rounded shrink-0 text-[22px] {{ request()->routeIs('admin.solicitudes.*') ? 'filled' : 'group-hover:text-slate-700' }}">inbox</span>
                            <span class="hide-on-collapse whitespace-nowrap">Bandeja de solicitudes</span>
                        </a>

                        <a href="#"
                           class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all {{ request()->routeIs('admin.calendario.*') ? 'is-active bg-active-bg text-active-text font-bold shadow-sm' : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="material-symbols-rounded shrink-0 text-[22px] {{ request()->routeIs('admin.calendario.*') ? 'filled' : 'group-hover:text-slate-700' }}">calendar_month</span>
                            <span class="hide-on-collapse whitespace-nowrap">Calendario Maestro</span>
                        </a>

                        <a href="#"
                           class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all {{ request()->routeIs('admin.usuarios.*') ? 'is-active bg-active-bg text-active-text font-bold shadow-sm' : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="material-symbols-rounded shrink-0 text-[22px] {{ request()->routeIs('admin.usuarios.*') ? 'filled' : 'group-hover:text-slate-700' }}">manage_accounts</span>
                            <span class="hide-on-collapse whitespace-nowrap">Gestión de Usuarios</span>
                        </a>
                    </nav>
                @elseif(Auth::user() && Auth::user()->role === 'solicitante')
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3 px-4 hide-on-collapse">Reservas</div>
                    <nav class="flex flex-col gap-1.5 mb-8">
                        <a href="{{ route('solicitante.reservas.historial') }}"
                           class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all {{ request()->routeIs('solicitante.historial.*') ? 'is-active bg-active-bg text-active-text font-bold shadow-sm' : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="material-symbols-rounded shrink-0 text-[22px] {{ request()->routeIs('solicitante.historial.*') ? 'filled' : 'group-hover:text-slate-700' }}">history</span>
                            <span class="hide-on-collapse whitespace-nowrap">Reservas</span>
                        </a>
                        <a href="{{ route('perfil.edit') }}"
                           class="sidebar-link group flex items-center center-on-collapse gap-4 rounded-xl px-4 py-3 text-[15px] transition-all {{ request()->routeIs('solicitante.historial.*') ? 'is-active bg-active-bg text-active-text font-bold shadow-sm' : 'text-slate-500 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="material-symbols-rounded shrink-0 text-[22px] {{ request()->routeIs('solicitante.historial.*') ? 'filled' : 'group-hover:text-slate-700' }}">history</span>
                            <span class="hide-on-collapse whitespace-nowrap">Perfil y Seguridad</span>
                        </a>
                    </nav>
                @endif

            </div>
            <div class="mt-4 pt-4 relative border-t border-slate-100">
                <div class="flex items-center px-1 py-2 relative center-on-collapse">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white font-bold shrink-0 shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="hide-on-collapse flex flex-col text-left overflow-hidden pl-3 pr-8 flex-1 min-w-0">

    <span class="text-[14px] font-bold text-slate-800 truncate">
        {{ Auth::user()->name ?? 'Usuario' }}
    </span>

    <span class="mt-0.5 text-[11px] font-medium text-slate-400 truncate">
        {{ Auth::user()->email ?? 'Sin correo' }}
    </span>

    <div class="mt-1.5">

        @if(Auth::user() && Auth::user()->role === 'admin')

            <span class="inline-flex items-center text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md uppercase tracking-wide">
                Admin
            </span>

        @elseif(Auth::user() && Auth::user()->role === 'solicitante')

            <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md uppercase tracking-wide">
                Solicitante
            </span>

        @endif

    </div>
</div>
                    <button type="button" onclick="abrirModalLogout()" class="absolute right-0 text-slate-400 hover:text-red-500 transition-colors p-2 bg-white hover:bg-red-50 rounded-xl z-10 shrink-0 hide-on-collapse" title="Cerrar Sesión">
                        <span class="material-symbols-rounded text-[20px]">logout</span>
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex flex-col h-screen w-full">
        <header id="top-header" class="fixed top-0 z-40 flex items-center justify-between bg-surface-light px-6 sm:px-10 py-4 h-[76px] shadow-[0_1px_2px_0_rgba(0,0,0,0.02)]">
            <div class="flex items-center gap-4">
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden flex items-center justify-center rounded-md p-2 text-slate-500 hover:text-primary hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-rounded">menu</span>
                </button>
                <div>
                    <h2 class="text-xl sm:text-[24px] font-bold text-slate-900 leading-none">@yield('titulo_pagina', 'Dashboard')</h2>
                    <p class="hidden sm:block text-[14px] text-slate-500 mt-1">@yield('subtitulo_pagina')</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                @yield('acciones_cabecera')
            </div>
        </header>

        <main id="main-content" class="flex-1 overflow-y-auto p-6 sm:p-10 mt-[76px] bg-background-light h-full">
            @yield('contenido')
        </main>
    </div>

    <div id="modalLogout" class="hidden relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-slate-100">
                    <div class="p-6">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600 mb-5">
                            <span class="material-symbols-rounded text-[28px]">logout</span>
                        </div>
                        <div class="text-center">
                            <h3 class="text-xl font-bold text-slate-900" id="modal-title">¿Cerrar Sesión?</h3>
                            <p class="mt-2 text-[15px] text-slate-500 leading-relaxed">¿Estás seguro de que deseas salir del sistema SRA?</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-slate-100">
                        <button type="button" id="btn-confirm-logout" onclick="cerrarSesionSeguro(this)" class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-[15px] font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                            Cerrar Sesión
                        </button>
                        <button type="button" onclick="cerrarModalLogout()" class="inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-[15px] font-semibold text-slate-700 shadow-sm border border-slate-200 hover:bg-slate-50 sm:w-auto transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
    </form>

    @yield('modales')
    @yield('scripts')

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const minIcon = document.getElementById('minimize-icon');
        const currentState = document.documentElement.getAttribute('data-sidebar');
        const sidebarScroll = document.getElementById('sidebar-nav-scroll');

        if (minIcon) {
            minIcon.innerText = currentState === 'closed'
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
                    link.addEventListener('click', guardarPosicionSidebar);
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
                form.setAttribute('aria-busy', 'true');

                window.setTimeout(() => {
                    form.querySelectorAll('button[type="submit"], input[type="submit"]')
                        .forEach((control) => {
                            control.disabled = true;
                            control.classList.add('cursor-wait', 'opacity-70');

                            if (control.tagName === 'BUTTON') {
                                control.dataset.textoOriginal = control.innerHTML;
                                control.innerHTML = '<span class="material-symbols-rounded animate-spin text-[18px]">sync</span> Procesando...';
                            }
                        });
                }, 0);
            });
        });
    }

    function abrirModalLogout() {
        const modal = document.getElementById('modalLogout');
        if (modal) modal.classList.remove('hidden');
    }

    function cerrarModalLogout() {
        const modal = document.getElementById('modalLogout');
        if (modal) modal.classList.add('hidden');
    }

    function toggleDesktopSidebar() {
        const html = document.documentElement;
        const minIcon = document.getElementById('minimize-icon');
        
        const isClosed = html.getAttribute('data-sidebar') === 'closed';
        const newState = isClosed ? 'open' : 'closed';
        
        html.setAttribute('data-sidebar', newState);
        localStorage.setItem('sidebarState', newState);
        
        if (minIcon) {
            minIcon.innerText = newState === 'closed' ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left';
        }

    }

    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }

    function cerrarSesionSeguro(btn) {
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-wait');
        btn.innerHTML = '<span class="material-symbols-rounded animate-spin text-[20px]">sync</span> Saliendo...';
        document.getElementById('logout-form').submit();
    }
    </script>
</body>
</html>
