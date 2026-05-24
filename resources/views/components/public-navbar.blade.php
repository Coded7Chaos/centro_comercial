<div x-data="{ mobileMenuOpen: false }">
    {{-- ============================================================
         STORE GLOBAL DE BÚSQUEDA (compartido entre todas las páginas)
         Cualquier vista puede consumirlo con $store.search.q
         ============================================================ --}}
    <script>
        document.addEventListener('alpine:init', () => {
            if (! Alpine.store('search')) {
                Alpine.store('search', { q: '' });
            }
        });
    </script>

    {{-- DESKTOP NAVBAR --}}
    <nav class="hidden md:block fixed top-4 left-4 right-4 z-[100]">
        <div class="flex items-center gap-3">
            {{-- Cuadro de navegación principal --}}
            <div class="glass-hud rounded-2xl px-4 py-2 flex items-center gap-4 shadow-2xl">
                {{-- Login --}}
                <a href="{{ route('login') }}"
                    class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-all border border-white/20 shadow-sm
                    {{ Request::is('/') ? 'text-slate-50' : 'text-slate-950'}}"
                    title="Iniciar Sesión">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>

                <div class="flex items-center gap-3">
                    <a href="/suscripciones"
                        class="px-4 py-2 rounded-xl text-sm tracking-widest transition-all relative group
                        {{ Request::is('suscripciones') ? 'glass-hud text-slate-900 border-white shadow-lg scale-105 font-bold' : 'text-slate-950 hover:text-slate-900 font-light' }}">
                        Suscripciones
                        @if(!Request::is('suscripciones'))
                            <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-white scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-center"></span>
                        @endif
                    </a>

                    <a href="/productos"
                        class="px-4 py-2 rounded-xl text-sm tracking-widest transition-all relative group
                        {{ Request::is('productos') ? 'glass-hud text-slate-900 border-white shadow-lg scale-105 font-bold' : 'text-slate-950 hover:text-slate-900 font-light' }}">
                        Productos
                        @if(!Request::is('productos'))
                            <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-white scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-center"></span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Search Bar global --}}
            <div class="relative flex-1 max-w-md glass-hud rounded-2xl shadow-2xl">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    placeholder="Buscar tiendas o productos..."
                    x-model="$store.search.q"
                    class="w-full bg-transparent pl-10 pr-10 py-2.5 text-sm font-medium text-slate-900 placeholder-slate-500 focus:outline-none rounded-2xl">
                <button
                    x-show="$store.search.q"
                    x-cloak
                    @click="$store.search.q = ''"
                    class="absolute inset-y-0 right-3 my-auto h-6 w-6 rounded-full bg-slate-900/10 hover:bg-slate-900/20 flex items-center justify-center text-slate-700"
                    title="Limpiar búsqueda">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- MOBILE NAVBAR --}}
    <nav class="md:hidden fixed top-4 left-4 right-4 z-[100]">
        <div class="flex items-center gap-2">
            <div class="glass-hud rounded-2xl px-3 py-2 flex items-center gap-3 shadow-xl">
                <button @click="mobileMenuOpen = true" class="p-2 rounded-lg hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-950 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div class="relative flex-1 glass-hud rounded-2xl shadow-xl">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    placeholder="Buscar..."
                    x-model="$store.search.q"
                    class="w-full bg-transparent pl-9 pr-8 py-2 text-xs font-medium text-slate-900 placeholder-slate-500 focus:outline-none rounded-2xl">
                <button
                    x-show="$store.search.q"
                    x-cloak
                    @click="$store.search.q = ''"
                    class="absolute inset-y-0 right-2 my-auto h-5 w-5 rounded-full bg-slate-900/10 flex items-center justify-center text-slate-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- MOBILE LATERAL MENU (Drawer) --}}
    <div x-show="mobileMenuOpen"
        x-cloak
        class="fixed inset-0 z-[200] md:hidden"
        role="dialog" aria-modal="true">

        <div x-show="mobileMenuOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm"
            @click="mobileMenuOpen = false"></div>

        <div x-show="mobileMenuOpen"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="absolute top-0 left-0 bottom-0 w-64 glass-hud border-r border-white/20 shadow-2xl flex flex-col p-6">

            <div class="flex items-center justify-between mb-8 text-slate-950 dark:text-white">
                <span class="text-[10px] font-black uppercase tracking-widest opacity-50">Menú</span>
                <button @click="mobileMenuOpen = false" class="p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex flex-col gap-4 flex-1">
                <a href="/" @click="mobileMenuOpen = false"
                    class="flex items-center gap-3 p-3 rounded-xl transition-all {{ Request::is('/') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-700 dark:text-white/80 hover:bg-white/10' }}">
                    <x-heroicon-o-building-office-2 class="w-5 h-5" />
                    <span class="text-[11px] font-bold tracking-widest uppercase">{{ $mallName }}</span>
                </a>

                <a href="/suscripciones" @click="mobileMenuOpen = false"
                    class="flex items-center gap-3 p-3 rounded-xl transition-all {{ Request::is('suscripciones') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-700 dark:text-white/80 hover:bg-white/10' }}">
                    <x-heroicon-o-credit-card class="w-5 h-5" />
                    <span class="text-[11px] font-bold tracking-widest uppercase">Suscripciones</span>
                </a>

                <a href="/productos" @click="mobileMenuOpen = false"
                    class="flex items-center gap-3 p-3 rounded-xl transition-all {{ Request::is('productos') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-700 dark:text-white/80 hover:bg-white/10' }}">
                    <x-heroicon-o-shopping-bag class="w-5 h-5" />
                    <span class="text-[11px] font-bold tracking-widest uppercase">Productos</span>
                </a>
            </div>

            <div class="pt-6 border-t border-white/20">
                <a href="{{ route('login') }}" class="flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all text-slate-900 dark:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-[11px] font-black tracking-widest uppercase">Iniciar sesión</span>
                </a>
            </div>
        </div>
    </div>
</div>
