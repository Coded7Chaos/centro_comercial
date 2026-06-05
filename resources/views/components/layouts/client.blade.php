@php
    $notifications = [];
    $user = Auth::user();
    if ($user && $user->cliente) {
        $cliente = $user->cliente;
        // Query unpaid cobros (where state is NOT 'pagado', NOT 'anulado', and NOT 'pendiente_confirmacion')
        $unpaidCobros = \App\Models\SuscripcionesCobros::whereHas('suscripcion', function ($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })
        ->whereNotIn('estado', ['pagado', 'anulado', 'pendiente_confirmacion'])
        ->get();

        foreach ($unpaidCobros as $cobro) {
            if (!$cobro->fecha_vencimiento) {
                continue;
            }
            $vencimiento = \Carbon\Carbon::parse($cobro->fecha_vencimiento)->startOfDay();
            $hoy = \Carbon\Carbon::now()->startOfDay();
            $diasParaPagar = (int)$hoy->diffInDays($vencimiento, false);

            if ($diasParaPagar >= 0 && $diasParaPagar <= 5) {
                $notifications[] = [
                    'tipo' => 'proximo',
                    'titulo' => 'Cobro Próximo a Vencer',
                    'mensaje' => "El cobro '{$cobro->concepto}' por Bs. " . number_format($cobro->monto, 2) . " vence en {$diasParaPagar} días (" . $vencimiento->format('d/m/Y') . ").",
                    'fecha' => $vencimiento,
                    'cobro_id' => $cobro->id,
                ];
            } elseif ($diasParaPagar < 0) {
                $diasAtraso = abs($diasParaPagar);
                $notifications[] = [
                    'tipo' => 'moroso',
                    'titulo' => 'Cobro Vencido (Moroso)',
                    'mensaje' => "El cobro '{$cobro->concepto}' por Bs. " . number_format($cobro->monto, 2) . " está vencido por {$diasAtraso} días.",
                    'fecha' => $vencimiento,
                    'cobro_id' => $cobro->id,
                ];
            }
        }

        // Load database notifications
        $dbNotifications = \App\Models\ClientNotification::where('cliente_id', $cliente->id)
            ->where('leido', false)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($dbNotifications as $dbNotif) {
            $notifications[] = [
                'id' => $dbNotif->id,
                'tipo' => $dbNotif->tipo,
                'titulo' => $dbNotif->titulo,
                'mensaje' => $dbNotif->mensaje,
                'fecha' => $dbNotif->created_at,
                'db' => true,
            ];
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal de Clientes - Mall Gran Vía' }}</title>
    
    <!-- Tailwind CSS & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
        }
        .glass-sidebar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-64 glass-sidebar text-slate-200 flex flex-col z-30 shrink-0">
        <div class="h-16 flex items-center justify-center border-b border-slate-800 px-6 gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-lg flex items-center justify-center shadow-lg">
                <span class="text-white font-extrabold text-sm">M</span>
            </div>
            <span class="font-extrabold text-lg tracking-tight text-white">Mall Gran Vía</span>
        </div>

        <!-- NAV LINKS -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="{{ route('cliente.dashboard') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition text-sm font-bold {{ request()->routeIs('cliente.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/></svg>
                Dashboard
            </a>
            
            <a href="{{ route('cliente.tienda') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition text-sm font-bold {{ request()->routeIs('cliente.tienda') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Mi Tienda
            </a>

            <a href="{{ route('cliente.personalizar') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition text-sm font-bold {{ request()->routeIs('cliente.personalizar') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Personalización de Inicio
            </a>

            <a href="{{ route('cliente.productos.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition text-sm font-bold {{ request()->routeIs('cliente.productos.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Mis Productos
            </a>

            <a href="{{ route('cliente.marcas.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition text-sm font-bold {{ request()->routeIs('cliente.marcas.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Mis Marcas
            </a>

            <a href="{{ route('cliente.categorias.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition text-sm font-bold {{ request()->routeIs('cliente.categorias.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Categorías
            </a>

            <a href="{{ route('cliente.estado-cuenta') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition text-sm font-bold {{ request()->routeIs('cliente.estado-cuenta') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Estado de Cuenta
            </a>
        </nav>

        <!-- FOOTER / USER -->
        <div class="p-4 border-t border-slate-800 space-y-4">
            <a href="/" target="_blank" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-xs font-bold transition text-slate-300">
                Ver Mall Público
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-white text-sm">
                    {{ substr(Auth::user()->nombres, 0, 1) }}{{ substr(Auth::user()->apellido_paterno, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-white truncate">{{ Auth::user()->nombres }}</div>
                    <div class="text-[10px] text-slate-500 truncate">Locatario</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN BODY -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- HEADER -->
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between shrink-0">
            <h2 class="text-xl font-bold text-slate-800">{{ $title ?? 'Portal de Clientes' }}</h2>
            
            <div class="flex items-center gap-4">
                <!-- Campanita de Notificaciones -->
                <div class="relative">
                    <button onclick="document.getElementById('notif-dropdown').classList.toggle('hidden')" class="relative p-2 text-slate-400 hover:text-slate-600 transition rounded-xl hover:bg-slate-100 focus:outline-none">
                        <!-- Bell Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if(count($notifications) > 0)
                            <span class="absolute top-1 right-1 w-4 h-4 bg-rose-500 text-white text-[9px] font-black rounded-full flex items-center justify-center">
                                {{ count($notifications) }}
                            </span>
                        @endif
                    </button>
                    
                    <!-- Dropdown -->
                    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-slate-200/80 rounded-3xl shadow-xl z-50 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Notificaciones</span>
                            <span class="text-[9px] font-black bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full">{{ count($notifications) }} Pendientes</span>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                            @if(count($notifications) === 0)
                                <div class="p-6 text-center text-xs text-slate-400 italic font-bold">No tienes notificaciones de cobro.</div>
                            @else
                                @foreach($notifications as $notif)
                                    <div class="p-4 hover:bg-slate-50 transition">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex gap-3">
                                                <span class="w-2 h-2 mt-1.5 rounded-full shrink-0 
                                                    {{ $notif['tipo'] === 'moroso' || $notif['tipo'] === 'danger' ? 'bg-rose-500' : '' }}
                                                    {{ $notif['tipo'] === 'proximo' || $notif['tipo'] === 'warning' ? 'bg-amber-500' : '' }}
                                                    {{ $notif['tipo'] === 'success' ? 'bg-emerald-500' : '' }}
                                                    {{ $notif['tipo'] === 'info' ? 'bg-indigo-500' : '' }}
                                                "></span>
                                                <div class="space-y-1">
                                                    <div class="text-xs font-bold text-slate-900">{{ $notif['titulo'] }}</div>
                                                    <p class="text-[11px] text-slate-500 leading-normal font-medium">{{ $notif['mensaje'] }}</p>
                                                </div>
                                            </div>
                                            @if(!empty($notif['db']))
                                                <form method="POST" action="{{ route('cliente.notificaciones.leer', $notif['id']) }}" class="shrink-0">
                                                    @csrf
                                                    <button type="submit" class="text-slate-300 hover:text-slate-600 transition p-1" title="Descartar">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                            <a href="{{ route('cliente.estado-cuenta') }}" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest block">Ver Estado de Cuenta</a>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </header>

        <!-- CONTENT CONTAINER -->
        <main class="flex-1 overflow-y-auto p-8">
            {{ $slot }}
        </main>
    </div>

    <script>
        document.addEventListener('click', function(event) {
            var dropdown = document.getElementById('notif-dropdown');
            if (dropdown) {
                var button = dropdown.previousElementSibling;
                if (!dropdown.classList.contains('hidden') && !dropdown.contains(event.target) && !button.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            }
        });
    </script>

    <!-- Toast Notifications (Alpine.js) -->
    <div x-data="{
            show: false,
            message: '',
            type: 'success',
            init() {
                @if(session('success'))
                    this.showToast('{{ session('success') }}', 'success');
                @endif
                @if(session('error'))
                    this.showToast('{{ session('error') }}', 'error');
                @endif
            },
            showToast(message, type) {
                this.message = message;
                this.type = type;
                this.show = true;
                setTimeout(() => {
                    this.show = false;
                }, 5000);
            }
         }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-8 translate-x-8 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 translate-x-2 scale-95"
         class="fixed bottom-6 right-6 z-[9999] max-w-sm w-full bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border p-4 flex items-start gap-3 pointer-events-auto"
         :class="type === 'success' ? 'border-emerald-500/30 shadow-emerald-500/5' : 'border-rose-500/30 shadow-rose-500/5'"
         x-cloak>
         
        <!-- Icon -->
        <div class="flex-shrink-0">
            <template x-if="type === 'success'">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
            </template>
            <template x-if="type === 'error'">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </template>
        </div>
        
        <!-- Text Content -->
        <div class="flex-1 min-w-0 pt-1">
            <p class="text-sm font-extrabold text-slate-800 tracking-tight" x-text="type === 'success' ? 'Operación Exitosa' : 'Ha ocurrido un error'"></p>
            <p class="text-xs font-semibold text-slate-500 mt-1 leading-relaxed" x-text="message"></p>
        </div>
        
        <!-- Close Button -->
        <button @click="show = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-xl transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</body>
</html>
