<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripciones y Tarifas - {{ \App\Models\Infraestructuras::first()?->nombre ?? 'Centro Comercial' }}</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        
        .glass-hud {
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
        }

        .tariff-card {
            transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .tariff-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -15px rgba(15,23,42,0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body class="overflow-x-hidden min-h-screen w-full select-none text-slate-900">

    {{-- MAIN BACKGROUND GRADIENT --}}
    <div class="fixed inset-0 z-0 bg-gradient-to-b from-[#e2e8f0] via-[#cbd5e1] to-[#94a3b8]"></div>

    <x-public-navbar />

    <main class="relative z-10 max-w-7xl mx-auto px-6 pt-32 pb-24">
        
        {{-- BACK TO MALL BUTTON --}}
        <div class="flex justify-center mb-8">
            <a href="/"
                class="inline-flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-white/70 border border-white/50 shadow-lg hover:shadow-xl hover:bg-white/90 transition-all duration-300">
                <div class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-900 text-white shadow-md">
                    <x-heroicon-o-building-office-2 class="w-5 h-5" />
                </div>
                <div class="text-left">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Volver al inicio</div>
                    <div class="font-black text-slate-900 text-xs">{{ \App\Models\Infraestructuras::first()?->nombre ?? 'Centro Comercial' }}</div>
                </div>
            </a>
        </div>

        {{-- HERO HEADER --}}
        <div class="flex flex-col items-center text-center space-y-4 mb-16">
            <span class="px-4 py-1.5 rounded-full bg-slate-900/10 border border-slate-900/10 text-slate-800 text-[10px] font-black tracking-widest uppercase">
                Planes Comerciales
            </span>
            <h1 class="text-4xl md:text-6xl font-black tracking-tighter text-slate-900 leading-none">
                Nuestras Suscripciones y <span class="text-indigo-700">Tarifas de Alquiler</span>
            </h1>
            <p class="max-w-2xl text-slate-600 font-medium text-sm md:text-base">
                Ofrecemos opciones de arrendamiento comercial escalables basadas en la dimensión física y requerimientos de tu marca. Encuentra el plan idóneo para ti.
            </p>
        </div>

        {{-- TARIFFS COMPARISON GRID --}}
        @if(isset($tarifas) && $tarifas->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($tarifas as $tarifa)
                    <div class="tariff-card glass-hud rounded-[2.5rem] border border-white/30 bg-white/10 p-8 flex flex-col justify-between h-full relative overflow-hidden">
                        
                        {{-- DECORATIVE SHINE OVERLAY --}}
                        <div class="absolute inset-0 bg-[linear-gradient(110deg,transparent_40%,rgba(255,255,255,0.15)_50%,transparent_60%)] opacity-80 pointer-events-none"></div>
                        
                        <div>
                            {{-- PLAN BADGE --}}
                            <div class="flex items-center justify-between mb-6">
                                <span class="px-3 py-1 rounded-full bg-indigo-600 text-white text-[9px] font-black uppercase tracking-wider">
                                    Plan {{ ucfirst($tarifa->tipo) }}
                                </span>
                                <x-heroicon-o-credit-card class="w-5 h-5 text-indigo-700" />
                            </div>

                            {{-- PLAN NAME --}}
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight leading-tight mb-2">
                                {{ $tarifa->etiqueta ?: ('Tarifa ' . ucfirst($tarifa->tipo)) }}
                            </h3>

                            {{-- M2 SIZE RANGE --}}
                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 mb-6 bg-slate-900/5 dark:bg-white/5 rounded-lg px-3 py-1.5 w-fit">
                                <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/></svg>
                                <span>Rango: {{ number_format($tarifa->tamano_min, 1) }}m² a {{ number_format($tarifa->tamano_max, 1) }}m²</span>
                            </div>
                        </div>

                        <div>
                            {{-- PRICE AREA --}}
                            <div class="border-t border-slate-900/10 pt-6 mb-6">
                                <span class="text-4xl font-black text-slate-900 tracking-tight">
                                    Bs. {{ number_format($tarifa->precio, 2) }}
                                </span>
                                <span class="text-xs font-bold text-slate-500">
                                    / {{ $tarifa->tipo }}
                                </span>
                            </div>

                            {{-- INITIATE BUTTON --}}
                            <a href="/login" 
                                class="w-full py-4 rounded-2xl bg-slate-900 text-white hover:bg-indigo-700 transition duration-300 font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 shadow-lg shadow-slate-900/20">
                                Iniciar Solicitud
                                <x-heroicon-o-arrow-right class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="glass-hud rounded-[3rem] p-12 text-center max-w-2xl mx-auto border border-white/30 flex flex-col items-center space-y-6">
                <div class="w-20 h-20 bg-slate-900/5 rounded-full flex items-center justify-center">
                    <x-heroicon-o-credit-card class="w-10 h-10 text-slate-500" />
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-900">Tarifas no disponibles</h3>
                    <p class="text-slate-600 font-medium text-sm">Actualmente no existen tarifas o planes de alquiler publicados en esta infraestructura. Comunícate con la oficina de administración para más información.</p>
                </div>
                <a href="/" class="px-8 py-3.5 bg-slate-900 text-white rounded-2xl font-bold hover:bg-indigo-700 transition duration-300 text-xs uppercase tracking-widest shadow-md">
                    Volver al Mall
                </a>
            </div>
        @endif

    </main>

    {{-- FOOTER --}}
    <footer class="relative z-10 max-w-7xl mx-auto px-6 py-12 border-t border-slate-900/10 mt-12">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center shadow-md">
                    <span class="text-white font-black text-xl">M</span>
                </div>
                <span class="font-black text-xl tracking-tighter text-slate-900">{{ \App\Models\Infraestructuras::first()?->nombre ?? 'Mall' }}</span>
            </div>
            <p class="text-slate-500 text-xs font-semibold">© 2026. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>
