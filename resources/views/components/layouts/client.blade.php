<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal de Clientes - Mall Gran Vía' }}</title>
    
    <!-- Tailwind CSS & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
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
            <!-- Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 font-semibold text-sm">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 font-semibold text-sm">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

</body>
</html>
