<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AgendaForo') }} - Acceso</title>

        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
        
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
                            "sans": ["Inter", "sans-serif"]
                        }
                    },
                },
            }
        </script>
        <style>
            body { font-family: 'Inter', sans-serif; }
            .material-symbols-rounded { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
            
            /* Forzar el botón principal de Laravel Breeze a usar el nuevo gradiente */
            .w-full.sm\:max-w-md button[type="submit"] {
                background-image: linear-gradient(to bottom right, #020617, #0f172a, #1e3a8a) !important;
                background-color: transparent !important;
                border: none !important;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .w-full.sm\:max-w-md button[type="submit"]:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.3);
            }
        </style>
    </head>
    <body class="font-sans text-text-main antialiased selection:bg-blue-600 selection:text-white flex h-screen overflow-hidden bg-white">
        
        <!-- Panel Izquierdo: Imagen e Identidad (Oculto en móviles) -->
        <div class="relative hidden lg:flex lg:w-1/2 xl:w-7/12 flex-col justify-between p-12 overflow-hidden shadow-2xl z-10">
            <!-- Imagen de fondo (Reemplaza este src por la foto real de tu auditorio) -->
            <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?q=80&w=2070&auto=format&fit=crop" alt="Auditorio" class="absolute inset-0 h-full w-full object-cover" />
            
            <!-- Superposición de gradiente moderno -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950/95 via-slate-900/90 to-blue-900/80 backdrop-blur-[2px]"></div>

            <!-- Header Izquierdo -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-slate-900 shadow-xl">
                    <span class="material-symbols-rounded text-[28px] filled">view_agenda</span>
                </div>
                <span class="text-2xl font-extrabold tracking-tight text-white">AgendaForo</span>
            </div>

            <!-- Contenido Central Izquierdo -->
            <div class="relative z-10 max-w-lg mt-auto mb-auto">
                <span class="inline-block py-1 px-3 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold tracking-widest uppercase mb-5">
                    Gestión Inteligente
                </span>
                <h1 class="text-4xl xl:text-5xl font-extrabold leading-tight text-white mb-6">
                    Reserva y administra espacios fácilmente.
                </h1>
                <p class="text-lg text-slate-300 leading-relaxed">
                    Bienvenido al Sistema de Reservas de Auditorio del Centro Nacional de Innovación Educativa y Desarrollo Docente.
                </p>
            </div>

            <!-- Footer Izquierdo -->
            <div class="relative z-10 text-sm font-medium text-slate-400 flex justify-between items-end">
                <div>
                    &copy; {{ date('Y') }} Tecnológico Nacional de México, Campus Roque. <br>
                    Ingeniería en TICs.
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Formulario de Login -->
        <div class="flex w-full lg:w-1/2 xl:w-5/12 flex-col justify-center px-8 sm:px-12 md:px-24 relative overflow-y-auto">
            
            <!-- Encabezado Móvil (Solo visible en pantallas pequeñas) -->
            <div class="lg:hidden flex flex-col items-center mb-10 text-center mt-8">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white shadow-xl shadow-blue-900/20 mb-5">
                    <span class="material-symbols-rounded text-[36px] filled">view_agenda</span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">AgendaForo</h1>
                <p class="text-sm font-bold uppercase tracking-widest text-slate-500 mt-2">TecNM Campus Roque</p>
            </div>

            <!-- Contenedor del Slot (Formulario) -->
            <div class="w-full max-w-sm mx-auto">
                
                
                {{ $slot }}
            </div>
            
            <!-- Footer Móvil -->
            <div class="lg:hidden mt-12 mb-8 text-center text-xs font-medium text-slate-400">
                &copy; {{ date('Y') }} TecNM Campus Roque. <br> Ingeniería en TICs.
            </div>
        </div>

    </body>
</html>