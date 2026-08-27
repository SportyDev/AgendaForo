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
            .bg-dots {
                background-color: #f4f7fe;
                background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
                background-size: 24px 24px;
            }
        </style>
    </head>
    <body class="font-sans text-text-main antialiased selection:bg-primary selection:text-white bg-dots">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/" class="flex flex-col items-center gap-4 group">
                    <div class="bg-primary flex h-20 w-20 items-center justify-center rounded-[1.25rem] text-white shadow-xl shadow-black/20 transition-transform group-hover:scale-105">
                        <span class="material-symbols-rounded text-5xl">event_seat</span>
                    </div>
                    <div class="text-center">
                        <h1 class="text-4xl font-extrabold tracking-tight leading-tight text-slate-900">AgendaForo</h1>
                        <p class="text-[12px] font-bold uppercase tracking-widest text-black mt-1">Sistema de Reservas de Auditorio</p>
                    </div>
                </a>
            </div>
            <div class="w-full sm:max-w-md mt-10 px-8 py-10 bg-surface-light shadow-2xl shadow-slate-200/60 overflow-hidden sm:rounded-[2rem] border border-slate-100 ring-1 ring-slate-100/50">
                {{ $slot }}
            </div>
            <div class="mt-10 text-center text-xs text-slate-400 font-medium tracking-wide">
                <p>&copy; {{ date('Y') }} Tecnológico Nacional de México, Campus Roque.</p>
                <p class="mt-1">Ingeniería en Tecnologías de la Información y Comunicaciones.</p>
            </div>
        </div>
    </body>
</html>
