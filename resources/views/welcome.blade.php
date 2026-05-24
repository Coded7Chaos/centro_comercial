<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mall['name'] ?? 'Centro Comercial' }} - Bienvenido</title>
    
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

        .store-sign {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.6), 0 2px 14px rgba(0,0,0,0.15);
        }

        .store-facade {
            backdrop-filter: blur(48px);
            -webkit-backdrop-filter: blur(48px);
            box-shadow: 0 24px 50px -18px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.5);
        }

        .rotate-container {
            transition: all 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes pulse-emerald {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
        .animate-pulse-emerald { animation: pulse-emerald 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc', 400: '#38bdf8',
                            500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1', 800: '#075985', 900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="overflow-hidden h-screen w-full select-none"
    x-data="{
        mall: @js($mall),
        contacto: @js($contacto),
        suscripcionesUrl: @js($suscripcionesUrl),
        floorIndex: {{ count($mall['floors']) - 1 }},
        storeIndex: 0,
        storeTurns: 0,
        activeStore: null,
        lock: false,
        viewMode: '3d', // '3d' or 'gallery'
        
        get currentFloor() { return this.mall.floors[this.floorIndex]; },
        get currentStore() { return this.currentFloor.stores[this.storeIndex]; },
        get nextStore() { 
            let idx = (this.storeIndex + 1) % this.currentFloor.stores.length;
            return this.currentFloor.stores[idx];
        },
        get prevStore() {
            let idx = (this.storeIndex - 1 + this.currentFloor.stores.length) % this.currentFloor.stores.length;
            return this.currentFloor.stores[idx];
        },

        setFloor(i) {
            if (this.lock) return;
            this.floorIndex = Math.max(0, Math.min(this.mall.floors.length - 1, i));
            this.storeIndex = Math.min(this.storeIndex, this.currentFloor.stores.length - 1);
            this.triggerLock();
        },

        setStore(i) {
            if (this.lock || this.currentFloor.stores.length <= 1) return;
            let prev = this.storeIndex;
            let total = this.currentFloor.stores.length;
            let next = ((i % total) + total) % total;
            
            let delta = next - prev;
            if (delta > total / 2) delta -= total;
            else if (delta < -total / 2) delta += total;
            
            this.storeTurns += delta;
            this.storeIndex = next;
            this.triggerLock();
        },

        triggerLock() {
            this.lock = true;
            setTimeout(() => this.lock = false, 800);
        },

        handleWheel(e) {
            if (this.activeStore || this.lock) return;
            const absX = Math.abs(e.deltaX);
            const absY = Math.abs(e.deltaY);
            if (Math.max(absX, absY) < 8) return;

            if (absX > absY) {
                if (e.deltaX > 0) this.setStore(this.storeIndex + 1);
                else this.setStore(this.storeIndex - 1);
            } else {
                if (e.deltaY < 0) this.setFloor(this.floorIndex - 1);
                else this.setFloor(this.floorIndex + 1);
            }
        },

        getAccentClasses(accent) {
            const palettes = {
                graphite: 'from-slate-500/70 via-slate-400/60 to-slate-600/70',
                steel: 'from-slate-400/70 via-slate-300/60 to-slate-500/70',
                platinum: 'from-slate-200/80 via-slate-100/70 to-slate-300/80',
                chrome: 'from-zinc-200/80 via-slate-100/70 to-zinc-300/80',
                champagne: 'from-amber-100/80 via-stone-100/70 to-amber-200/80',
                brass: 'from-amber-200/80 via-yellow-200/70 to-amber-300/80',
                obsidian: 'from-slate-600/75 via-zinc-600/65 to-slate-700/75',
                pearl: 'from-stone-100/80 via-slate-100/70 to-stone-200/80',
                copper: 'from-orange-200/80 via-amber-200/70 to-orange-300/80',
                gunmetal: 'from-slate-500/75 via-zinc-500/65 to-slate-600/75',
                silk: 'from-rose-100/80 via-stone-100/70 to-rose-200/80',
                jade: 'from-emerald-100/80 via-stone-100/70 to-teal-100/80',
            };
            return palettes[accent] || palettes.graphite;
        },

        getMonogram(name) {
            const parts = name.replace(/[^a-zA-ZÀ-ÿ ]/g, '').trim().split(/\s+/);
            if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
            return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        },

        // Salta al piso/tienda que matchea con el search global del navbar
        jumpToSearchMatch(q) {
            const needle = (q || '').toLowerCase().trim();
            if (!needle) return;
            for (let fi = 0; fi < this.mall.floors.length; fi++) {
                const stores = this.mall.floors[fi].stores;
                for (let si = 0; si < stores.length; si++) {
                    const s = stores[si];
                    const hay = (s.nombre || '').toLowerCase()
                        + ' ' + (s.marca || '').toLowerCase()
                        + ' ' + (s.numero || '').toString().toLowerCase();
                    if (hay.includes(needle)) {
                        this.setFloor(fi);
                        this.setStore(si);
                        return;
                    }
                }
            }
        }
    }"
    x-init="$watch('$store.search.q', q => jumpToSearchMatch(q))"
    @wheel="handleWheel"
    @keydown.up.window="setFloor(floorIndex - 1)"
    @keydown.down.window="setFloor(floorIndex + 1)"
    @keydown.left.window="setStore(storeIndex - 1)"
    @keydown.right.window="setStore(storeIndex + 1)"
    @keydown.escape.window="activeStore = null"
>
    <x-public-navbar />

    {{-- MAIN BACKGROUND GRADIENT --}}
    <div class="absolute inset-0 z-0 bg-gradient-to-b from-[#e2e8f0] via-[#cbd5e1] to-[#94a3b8]"></div>

    {{-- PANORAMA BACKGROUND --}}
    <div class="absolute inset-0 z-10 transition-all duration-1000 ease-[cubic-bezier(0.22,1,0.36,1)]"
        :key="'bg-' + floorIndex">
        <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=2000&q=80" 
            class="h-full w-full object-cover saturate-[0.85] brightness-[1.02]">
        
        <div class="absolute inset-0 bg-gradient-to-b from-white/20 via-transparent to-slate-900/30"></div>
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 55%, transparent 50%, rgba(15,23,42,0.45) 100%)"></div>
    </div>

    {{-- TOP CEILING SHADOW --}}
    <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-slate-900/30 via-slate-900/10 to-transparent z-20"></div>

    {{-- FLOOR MARBLE REFLECTION --}}
    <div class="absolute inset-x-0 bottom-0 h-1/3 z-20" 
        style="background: linear-gradient(to top, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.08) 40%, transparent 100%); backdrop-filter: blur(2px);"></div>

    {{-- SECTION BANNER --}}
    <div class="absolute inset-x-0 top-20 z-40 flex justify-center px-4 pointer-events-none">
        <div class="pointer-events-auto flex items-center gap-3 md:gap-5 rounded-2xl border border-white/40 bg-slate-900/55 px-4 py-2 md:px-6 md:py-4 backdrop-blur-2xl shadow-2xl transition-all duration-500"
            :key="'banner-' + floorIndex + '-' + storeIndex">
            <div class="pr-3 md:pr-5 border-r border-white/20 text-center md:text-left">
                <div class="text-white/70 text-[8px] md:text-[10px] tracking-[0.3em] uppercase" x-text="'PISO ' + currentFloor.displayLevel + ' · LOCAL ' + currentStore.numero"></div>
                <div class="text-white text-xl md:text-3xl font-black tracking-tighter" x-text="currentStore.nombre"></div>
            </div>
            <div class="hidden sm:block">
                <div class="text-white/95 font-semibold text-[11px] md:text-sm" x-text="currentFloor.name"></div>
                <div class="text-white/70 text-[9px] md:text-xs" x-text="currentFloor.vibe"></div>
            </div>
        </div>
    </div>

    {{-- INTERIOR CONTAINER --}}
    <div class="absolute inset-0 z-30" 
        :style="viewMode === '3d' ? 'perspective: 1600px; perspective-origin: 50% 50%' : ''">
        
        <div class="relative h-full w-full rotate-container"
            :style="viewMode === '3d' 
                ? 'transform-style: preserve-3d; transform: translateZ(-' + (window.innerWidth < 768 ? 600 : window.innerWidth/1.5) + 'px) rotateY(' + (-storeTurns * (360 / currentFloor.stores.length)) + 'deg);'
                : 'transform: none;'">
            
            <template x-for="(store, i) in currentFloor.stores" :key="store.id">
                <div class="absolute inset-0 flex items-center md:justify-center transition-all duration-700"
                    :style="viewMode === '3d'
                        ? 'transform: rotateY(' + (i * (360 / currentFloor.stores.length)) + 'deg) translateZ(' + (window.innerWidth < 768 ? 600 : window.innerWidth/1.5) + 'px); backface-visibility: hidden; transform-style: preserve-3d;'
                        : 'transform: translateX(' + ((i - storeIndex) * (window.innerWidth < 768 ? 260 : 480)) + 'px) scale(' + (1 - Math.abs(i - storeIndex) * 0.15) + '); opacity: ' + (1 - Math.abs(i - storeIndex) * 0.4) + '; z-index: ' + (20 - Math.abs(i - storeIndex)) + ';'">
                    
                    <div class="w-[85vw] md:w-[450px] h-[60vh] md:h-[550px] ml-4 md:ml-0">
                        <button @click="activeStore = store"
                            class="group relative w-full h-full cursor-pointer text-left transition-all duration-500 active:scale-95 flex flex-col">
                            
                            {{-- AWNING --}}
                            <div class="relative h-20 md:h-24 rounded-t-[20px] px-4 md:px-6 flex items-center justify-between gap-4 overflow-hidden border-t border-x border-white/50 store-sign"
                                :class="store.is_alquilada
                                    ? 'bg-gradient-to-b from-slate-600 to-slate-700'
                                    : 'bg-gradient-to-b from-emerald-600 to-emerald-800'">
                                <div class="absolute inset-0 bg-[linear-gradient(110deg,transparent_30%,rgba(255,255,255,0.4)_45%,transparent_60%)] opacity-70 pointer-events-none"></div>
                                <div class="relative flex items-center gap-3 md:gap-4 flex-1 min-w-0">
                                    <div class="flex h-10 w-10 md:h-14 md:w-14 items-center justify-center rounded-xl border border-white/60 bg-white/30 backdrop-blur shadow-inner text-sm md:text-xl font-black text-white uppercase" x-text="getMonogram(store.nombre)"></div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-lg md:text-2xl font-black tracking-tight truncate text-white" x-text="store.nombre"></div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[8px] md:text-[10px] tracking-[0.2em] font-bold opacity-80 uppercase truncate text-white" x-text="'Local ' + store.numero"></span>
                                            <span class="text-[8px] md:text-[9px] font-black tracking-[0.2em] uppercase px-1.5 py-0.5 rounded border text-white whitespace-nowrap"
                                                :class="store.is_alquilada
                                                    ? 'bg-white/20 border-white/50'
                                                    : 'bg-emerald-400/30 border-emerald-200/80'"
                                                x-text="store.is_alquilada ? 'Alquilada' : 'Disponible'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- FACADE --}}
                            <div class="relative flex-1 overflow-hidden border-x border-b border-white/60 store-facade flex"
                                :class="store.is_alquilada
                                    ? 'bg-gradient-to-b from-slate-500/80 via-white/50 to-slate-300/60'
                                    : 'bg-gradient-to-b from-emerald-200/70 via-white/60 to-emerald-100/70'">

                                {{-- Vidriera izquierda: producto o panel "disponible" --}}
                                <div class="relative flex-1 border-r border-white/30"
                                    :class="store.is_alquilada
                                        ? 'bg-gradient-to-br from-white/50 via-slate-200/40 to-slate-300/50'
                                        : 'bg-gradient-to-br from-emerald-50/70 via-white/60 to-emerald-100/60'">
                                    <template x-if="store.is_alquilada">
                                        <div class="absolute inset-2 md:inset-4 grid grid-cols-1 gap-2 md:gap-4">
                                            <template x-for="p in store.productos.slice(0,2)" :key="p.id">
                                                <div class="relative overflow-hidden rounded-lg border border-white/40 bg-black/10 shadow-inner h-full">
                                                    <img :src="p.imagenes?.[0]?.url" class="h-full w-full object-cover opacity-90 saturate-[0.8] brightness-95">
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="! store.is_alquilada">
                                        <div class="absolute inset-2 md:inset-4 rounded-lg border-2 border-dashed border-emerald-400/60 bg-emerald-50/40 flex items-center justify-center p-2">
                                            <div class="text-center">
                                                <div class="text-emerald-700 text-[10px] md:text-xs font-black tracking-[0.2em] uppercase">Espacio Libre</div>
                                                <div class="text-emerald-800/80 text-[9px] md:text-[11px] font-bold mt-1">Listo para alquilar</div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Puerta central --}}
                                <div class="relative flex w-[80px] md:w-[130px] flex-col items-center border-x border-white/35"
                                    :class="store.is_alquilada
                                        ? 'bg-gradient-to-b from-white/50 via-slate-100/60 to-slate-300/70'
                                        : 'bg-gradient-to-b from-emerald-100/60 via-white/60 to-emerald-200/70'">
                                    <div class="absolute inset-x-2 md:inset-x-4 top-4 bottom-7 rounded-t-lg border border-white/50 bg-gradient-to-b from-white/70 via-white/50 to-white/60 backdrop-blur-md shadow-inner overflow-hidden">
                                        <div class="absolute top-3 left-0 right-0 text-[7px] md:text-[9px] font-bold text-slate-700/80 tracking-[0.3em] text-center"
                                            x-text="store.is_alquilada ? 'ENTRAR' : 'ALQUILAR'"></div>
                                    </div>
                                    <div class="absolute right-2 md:right-6 top-1/2 h-8 w-1.5 md:w-2 -translate-y-1/2 rounded-full bg-gradient-to-b from-slate-200 via-slate-400 to-slate-500 shadow-md"></div>
                                </div>

                                {{-- Vidriera derecha --}}
                                <div class="relative flex-1 border-l border-white/30"
                                    :class="store.is_alquilada
                                        ? 'bg-gradient-to-bl from-white/50 via-slate-200/40 to-slate-300/50'
                                        : 'bg-gradient-to-bl from-emerald-50/70 via-white/60 to-emerald-100/60'">
                                    <template x-if="store.is_alquilada && store.productos[2]">
                                        <div class="absolute inset-2 md:inset-4">
                                            <div class="h-full relative overflow-hidden rounded-lg border border-white/40 bg-black/10 shadow-inner">
                                                <img :src="store.productos[2].imagenes?.[0]?.url" class="h-full w-full object-cover opacity-90 saturate-[0.8] brightness-95">
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="! store.is_alquilada">
                                        <div class="absolute inset-2 md:inset-4 rounded-lg border-2 border-dashed border-emerald-400/60 bg-emerald-50/40 flex items-center justify-center">
                                            <svg class="w-8 h-8 md:w-10 md:h-10 text-emerald-600/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- HUD ELEMENTS --}}
    
    {{-- Top Bar Actions --}}
    <div class="fixed top-24 right-6 z-50 flex items-center gap-4 pointer-events-none">
        <div class="pointer-events-auto flex items-center gap-2 p-1.5 rounded-2xl glass-hud shadow-xl">
            <button @click="viewMode = '3d'" 
                class="p-2 rounded-xl transition-all"
                :class="viewMode === '3d' ? 'bg-slate-900 text-white shadow-lg scale-105' : 'text-slate-500 hover:text-slate-900'">
                <x-heroicon-o-square-3-stack-3d class="w-5 h-5" />
            </button>
            <button @click="viewMode = 'gallery'" 
                class="p-2 rounded-xl transition-all"
                :class="viewMode === 'gallery' ? 'bg-slate-900 text-white shadow-lg scale-105' : 'text-slate-500 hover:text-slate-900'">
                <x-heroicon-o-queue-list class="w-5 h-5" />
            </button>
        </div>

        <div class="pointer-events-auto hidden md:flex items-center gap-3 rounded-2xl glass-hud px-5 py-3 text-[11px] font-bold text-slate-700">
            <span class="flex items-center gap-1.5"><kbd class="bg-white/40 border border-white/60 px-2 py-0.5 rounded text-[9px]">↑↓</kbd> PISOS</span>
            <span class="text-slate-300">·</span>
            <span class="flex items-center gap-1.5"><kbd class="bg-white/40 border border-white/60 px-2 py-0.5 rounded text-[9px]">←→</kbd> TIENDAS</span>
        </div>
    </div>

    {{-- Elevator Panel (Right) --}}
    <div class="fixed right-2 md:right-6 top-1/2 -translate-y-1/2 z-50">
        <div class="flex flex-col items-center gap-2 md:gap-3 rounded-2xl glass-hud p-1 md:p-3">
            <button @click="setFloor(floorIndex - 1)" :disabled="floorIndex === 0"
                class="rounded-lg border border-white/50 bg-white/70 p-1 md:p-2 text-slate-700 hover:bg-white disabled:opacity-30 transition shadow-sm">
                <x-heroicon-o-chevron-up class="w-4 h-4 md:w-5 md:h-5" />
            </button>
            <div class="flex flex-col gap-1.5 md:gap-2">
                <template x-for="(f, i) in mall.floors" :key="f.level">
                    <button @click="setFloor(i)"
                        class="relative h-8 w-10 md:h-11 md:w-14 rounded-lg border transition flex flex-col items-center justify-center"
                        :class="i === floorIndex ? 'border-white bg-white text-slate-950 shadow-lg' : 'border-white/30 bg-white/10 text-slate-600 hover:bg-white/20'">
                        <div class="text-sm md:text-lg font-black leading-none" x-text="f.displayLevel"></div>
                        <div x-show="i === floorIndex" class="absolute -left-1 top-1/2 -translate-y-1/2 w-1 h-4 md:h-6 bg-emerald-500 rounded-full"></div>
                    </button>
                </template>
            </div>
            <button @click="setFloor(floorIndex + 1)" :disabled="floorIndex === mall.floors.length - 1"
                class="rounded-lg border border-white/50 bg-white/70 p-1 md:p-2 text-slate-700 hover:bg-white disabled:opacity-30 transition shadow-sm">
                <x-heroicon-o-chevron-down class="w-4 h-4 md:w-5 md:h-5" />
            </button>
        </div>
    </div>

    {{-- Compass (Left) --}}
    <div class="fixed left-8 top-1/2 -translate-y-1/2 z-50 hidden lg:block" x-show="viewMode === '3d'">
        <div class="rounded-[2.5rem] glass-hud p-5 flex flex-col items-center gap-4">
            <div class="flex items-center gap-2 text-[10px] font-black tracking-[0.2em] text-slate-50 uppercase">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Brújula
            </div>
            <div class="relative h-48 w-48">
                <div class="absolute inset-0 rounded-full border-2 border-white/40 bg-white/5 shadow-inner"></div>
                <div class="absolute inset-4 rounded-full border border-white/20"></div>
                <div class="absolute inset-0 transition-transform duration-700 ease-out" 
                    :style="'transform: rotate(' + (-storeTurns * (360 / currentFloor.stores.length)) + 'deg)'">
                    <template x-for="(s, i) in currentFloor.stores" :key="s.id">
                        <button @click="setStore(i)"
                            class="absolute left-1/2 top-1/2 -ml-2.5 -mt-2.5 h-5 w-5 rounded-full border flex items-center justify-center transition-all duration-300"
                            :class="i === storeIndex ? 'border-emerald-400 bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)] scale-125' : 'border-white/30 bg-white/20 hover:bg-white/40'"
                            :style="'transform: rotate(' + (i * (360 / currentFloor.stores.length)) + 'deg) translateY(-76px) rotate(' + (-i * (360 / currentFloor.stores.length)) + 'deg)'">
                        </button>
                    </template>
                </div>
                <div class="absolute left-1/2 top-1/2 -ml-2 -mt-2 h-4 w-4 bg-slate-800 rounded-full border-2 border-white/60 shadow-lg"></div>
            </div>
            <div class="text-center">
                <div class="text-[8px] font-black text-white tracking-[0.2em] uppercase mb-0.5" x-text="'Tienda ' + (storeIndex + 1) + ' / ' + currentFloor.stores.length"></div>
                <div class="text-xs font-black text-white tracking-tight truncate max-w-[120px]" x-text="currentStore.nombre"></div>
            </div>
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <div class="fixed bottom-4 md:bottom-8 left-1/2 -translate-x-1/2 z-50 w-[90vw] md:w-auto">
        <div class="flex items-center justify-between md:justify-center gap-2 md:gap-4 rounded-2xl md:rounded-[2rem] glass-hud p-2 md:p-3">
            <button @click="setStore(storeIndex - 1)"
                class="rounded-xl border border-white/50 bg-white/60 p-2 md:p-3 text-slate-700 hover:bg-white transition shadow-sm active:scale-95">
                <x-heroicon-o-chevron-left class="w-4 h-4 md:w-6 md:h-6" />
            </button>
            <div class="px-2 md:px-6 text-center flex-1 md:min-w-[300px]">
                <div class="text-[7px] md:text-[9px] font-black tracking-[0.2em] text-slate-500 uppercase mb-0.5">Siguiente Tienda</div>
                <div class="text-slate-900 font-black text-[10px] md:text-lg tracking-tight truncate max-w-[150px] md:max-w-none mx-auto" x-text="nextStore.nombre"></div>
            </div>
            <button @click="setStore(storeIndex + 1)"
                class="rounded-xl border border-white/50 bg-white/60 p-2 md:p-3 text-slate-700 hover:bg-white transition shadow-sm active:scale-95">
                <x-heroicon-o-chevron-right class="w-4 h-4 md:w-6 md:h-6" />
            </button>
        </div>
    </div>

    {{-- STORE MODAL --}}
    <template x-teleport="body">
        <div x-show="activeStore" 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-8"
            x-transition.opacity.duration.300ms x-cloak>
            
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-xl" @click="activeStore = null"></div>

            <div x-show="activeStore"
                class="relative w-full max-w-5xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl rounded-[3rem] shadow-2xl border border-white/40 overflow-hidden flex flex-col max-h-[90vh]"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-12"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                
                {{-- Modal Header --}}
                <div class="relative p-6 md:p-10 text-white"
                    :class="activeStore?.is_alquilada
                        ? 'bg-gradient-to-br from-slate-600 to-slate-800'
                        : 'bg-gradient-to-br from-emerald-600 to-emerald-800'">
                    <button
                        type="button"
                        @click.stop="activeStore = null"
                        class="absolute top-4 right-4 md:top-8 md:right-8 z-20 p-2 rounded-full bg-white/20 backdrop-blur hover:bg-white/40 transition cursor-pointer">
                        <svg class="w-5 h-5 md:w-6 md:h-6 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="relative flex items-center gap-4 md:gap-8">
                        <div class="h-16 w-16 md:h-24 md:w-24 rounded-2xl bg-white/30 backdrop-blur border border-white/50 flex items-center justify-center text-xl md:text-4xl font-black uppercase"
                            x-text="getMonogram(activeStore?.nombre || '')"></div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-white/70 text-[8px] md:text-xs font-black tracking-[0.3em] uppercase" x-text="'Local ' + activeStore?.numero"></span>
                                <span class="text-[9px] md:text-[11px] font-black tracking-[0.2em] uppercase px-2 py-0.5 rounded-full border border-white/40 bg-white/15"
                                    x-text="activeStore?.is_alquilada ? 'Alquilada' : 'Disponible'"></span>
                            </div>
                            <h2 class="text-2xl md:text-5xl font-black tracking-tighter truncate" x-text="activeStore?.nombre"></h2>
                            <p class="text-white/90 text-sm md:text-lg font-medium italic mt-1 line-clamp-2" x-text="activeStore?.descripcion"></p>
                        </div>
                    </div>
                </div>

                {{-- Modal Content --}}
                <div class="flex-1 overflow-y-auto p-6 md:p-10 space-y-8">

                    {{-- =========================================================
                         TIENDA ALQUILADA → catálogo
                         ========================================================= --}}
                    <template x-if="activeStore?.is_alquilada">
                        <div class="space-y-6">
                            {{-- Datos de la tienda --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                                <template x-if="activeStore?.marca">
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/40">
                                        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-slate-500">Marca</div>
                                        <div class="text-base md:text-lg font-black text-slate-900 dark:text-white truncate" x-text="activeStore?.marca"></div>
                                    </div>
                                </template>
                                <template x-if="activeStore?.inquilino">
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/40">
                                        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-slate-500">Inquilino</div>
                                        <div class="text-base md:text-lg font-black text-slate-900 dark:text-white truncate" x-text="activeStore?.inquilino"></div>
                                    </div>
                                </template>
                                <template x-if="activeStore?.telefono">
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/40">
                                        <div class="text-[10px] font-bold tracking-[0.2em] uppercase text-slate-500">Teléfono local</div>
                                        <div class="text-base md:text-lg font-black text-slate-900 dark:text-white truncate" x-text="activeStore?.telefono"></div>
                                    </div>
                                </template>
                            </div>

                            <h3 class="text-xl md:text-2xl font-black flex items-center gap-3">
                                <div class="h-8 w-8 md:h-10 md:w-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg">
                                    <x-heroicon-o-shopping-bag class="w-4 h-4 md:w-6 md:h-6 text-white" />
                                </div>
                                Catálogo de Productos
                            </h3>

                            <template x-if="(activeStore?.productos?.length ?? 0) === 0">
                                <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-8 text-center text-slate-500 italic">
                                    Esta tienda aún no cargó productos a su catálogo.
                                </div>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-8">
                                <template x-for="p in activeStore?.productos" :key="p.id">
                                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-3 md:p-4">
                                        <div class="aspect-square rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-700 mb-3 md:mb-4">
                                            <img :src="p.imagenes?.[0]?.url" class="h-full w-full object-cover">
                                        </div>
                                        <div class="space-y-1 md:space-y-2">
                                            <h4 class="font-black text-slate-800 dark:text-white truncate text-sm md:text-lg" x-text="p.nombre"></h4>
                                            <div class="flex items-center justify-between">
                                                <span class="text-lg md:text-2xl font-black text-emerald-600 dark:text-emerald-400" x-text="'Bs. ' + Number(p.precio).toFixed(2)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- =========================================================
                         TIENDA DISPONIBLE → invitación + contacto + CTA
                         ========================================================= --}}
                    <template x-if="! activeStore?.is_alquilada">
                        <div class="space-y-6">
                            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 p-6 md:p-8 text-center">
                                <div class="inline-flex items-center gap-2 text-[10px] md:text-xs font-black tracking-[0.3em] uppercase text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-300 dark:border-emerald-800 px-3 py-1 rounded-full mb-3">
                                    <svg class="w-3 h-3 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="6"/></svg>
                                    Espacio disponible
                                </div>
                                <h3 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white">
                                    Este local está libre para alquilar
                                </h3>
                                <p class="text-slate-600 dark:text-slate-300 mt-2 max-w-2xl mx-auto text-sm md:text-base">
                                    Si te interesa este espacio en <span class="font-bold" x-text="mall.name"></span>, escríbenos para coordinar una visita, ver los precios y conocer los tipos de contrato disponibles.
                                </p>

                                <template x-if="activeStore?.tamano">
                                    <div class="inline-flex items-center gap-2 mt-4 text-xs md:text-sm font-bold text-emerald-800 dark:text-emerald-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/></svg>
                                        <span x-text="'Tamaño aproximado: ' + activeStore?.tamano + ' m²'"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5 bg-white dark:bg-slate-900/60">
                                    <div class="text-[10px] md:text-xs font-black tracking-[0.2em] uppercase text-slate-500 mb-3">Contacto del administrador</div>
                                    <div class="space-y-2 text-sm md:text-base">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                                <x-heroicon-o-user class="w-4 h-4 text-slate-600 dark:text-slate-300" />
                                            </div>
                                            <span class="font-bold text-slate-800 dark:text-white" x-text="contacto.nombre"></span>
                                        </div>
                                        <a :href="'mailto:' + contacto.email + '?subject=Consulta%20de%20alquiler%20-%20Local%20' + (activeStore?.numero ?? '')"
                                            class="flex items-center gap-3 group hover:bg-slate-50 dark:hover:bg-slate-800/60 rounded-lg p-1 -mx-1 transition">
                                            <div class="h-9 w-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                                <x-heroicon-o-envelope class="w-4 h-4 text-slate-600 dark:text-slate-300" />
                                            </div>
                                            <span class="text-slate-700 dark:text-slate-200 group-hover:text-emerald-700 truncate" x-text="contacto.email"></span>
                                        </a>
                                        <a :href="'tel:' + (contacto.telefono ?? '').replace(/\s+/g,'')"
                                            class="flex items-center gap-3 group hover:bg-slate-50 dark:hover:bg-slate-800/60 rounded-lg p-1 -mx-1 transition">
                                            <div class="h-9 w-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                                <x-heroicon-o-phone class="w-4 h-4 text-slate-600 dark:text-slate-300" />
                                            </div>
                                            <span class="text-slate-700 dark:text-slate-200 group-hover:text-emerald-700" x-text="contacto.telefono"></span>
                                        </a>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-emerald-300 dark:border-emerald-800 p-5 bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/40 dark:to-slate-900/60 flex flex-col">
                                    <div class="text-[10px] md:text-xs font-black tracking-[0.2em] uppercase text-emerald-700 dark:text-emerald-300 mb-3">Tipos de contrato</div>
                                    <p class="text-slate-700 dark:text-slate-200 text-sm md:text-base flex-1">
                                        Tenemos tarifas mensuales, trimestrales, semestrales y anuales según el tamaño del local. Revisa las opciones publicadas y elige la que mejor se ajuste a tu proyecto.
                                    </p>
                                    <a :href="suscripcionesUrl"
                                        class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black px-5 py-3 text-sm md:text-base shadow-lg transition">
                                        Ver las suscripciones disponibles
                                        <x-heroicon-o-arrow-right class="w-4 h-4 md:w-5 md:h-5" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

</body>
</html>
