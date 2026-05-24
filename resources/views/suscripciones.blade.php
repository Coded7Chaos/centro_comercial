<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripciones - {{ \App\Models\Infraestructuras::first()?->nombre ?? 'Mall' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-hud {
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(24px);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen">
    <x-public-navbar />

    <main class="max-w-5xl mx-auto px-6 pt-32">
        {{-- Botón Mall al inicio --}}
        <div class="flex justify-center mb-8">
            <a href="/"
                class="inline-flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div class="h-9 w-9 flex items-center justify-center rounded-xl bg-gradient-to-br from-slate-200 via-slate-100 to-slate-300 border border-white shadow-inner">
                    <x-heroicon-o-building-office-2 class="w-5 h-5 text-slate-700" />
                </div>
                <div class="text-left">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Volver al mall</div>
                    <div class="font-bold text-slate-900 text-sm">{{ \App\Models\Infraestructuras::first()?->nombre ?? 'Mall' }}</div>
                </div>
            </a>
        </div>

        <div class="text-center">
            <h1 class="text-5xl lg:text-7xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-600 dark:from-indigo-400 dark:to-emerald-400">
                Página de Suscripciones
            </h1>
            <p class="mt-6 text-slate-500 dark:text-slate-400 text-lg">Próximamente podrás gestionar tus suscripciones aquí.</p>
        </div>
    </main>
</body>
</html>
