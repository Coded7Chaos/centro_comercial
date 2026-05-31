@php
    $infraestructuras = \App\Models\Infraestructuras::with([
        'pisosInfraestructura.tiendas.productos.imagenes',
        'pisosInfraestructura.tiendas.productos.categoria',
        'pisosInfraestructura.tiendas.productos.marca',
    ])->get();

    $data = $infraestructuras->map(function($infra) {
        $allProducts = collect();
        foreach($infra->pisosInfraestructura as $piso) {
            foreach($piso->tiendas as $tienda) {
                foreach($tienda->productos as $producto) {
                    $allProducts->push([
                        'id'             => $producto->id,
                        'nombre'         => $producto->nombre,
                        'descripcion'    => $producto->descripcion,
                        'precio'         => (float) $producto->precio,
                        'estado'         => $producto->estado,
                        'tienda_nombre'  => $tienda->nombre,
                        'tienda_numero'  => $tienda->numero,
                        'tienda_id'      => $tienda->id,
                        'piso_nombre'    => $piso->nombre,
                        'piso_numero'    => $piso->numero ?: $piso->nombre,
                        'categoria'      => $producto->categoria?->nombre ?? 'General',
                        'marca'          => $producto->marca?->nombre,
                        'imagenes'       => $producto->imagenes->map(fn($i) => ['url' => str_starts_with($i->url, 'http') ? $i->url : \Illuminate\Support\Facades\Storage::url($i->url)])->values(),
                    ]);
                }
            }
        }
        return [
            'id'        => $infra->id,
            'nombre'    => $infra->nombre,
            'ubicacion' => $infra->ubicacion,
            'productos' => $allProducts->values(),
        ];
    });
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-hud {
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
        }

        .infra-tag { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        .product-card {
            transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -15px rgba(15,23,42,0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
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
<body class="overflow-x-hidden min-h-screen w-full select-none text-slate-900"
    x-data="{
        infras: @js($data),
        selectedInfraId: @js($data->first()['id'] ?? null),
        activeProduct: null,

        get currentInfra() {
            return this.infras.find(i => i.id === this.selectedInfraId);
        },

        get filteredProducts() {
            const q = this.$store.search?.q || '';
            const base = this.currentInfra ? this.currentInfra.productos : [];
            if (!q) return base;
            return base.filter(p =>
                window.matchesSearchQuery(p.nombre, q)
                || window.matchesSearchQuery(p.tienda_nombre, q)
                || window.matchesSearchQuery(p.categoria, q)
                || window.matchesSearchQuery(p.marca, q)
            );
        }
    }"
    @keydown.escape.window="activeProduct = null">

    {{-- MAIN BACKGROUND GRADIENT --}}
    <div class="fixed inset-0 z-0 bg-gradient-to-b from-[#e2e8f0] via-[#cbd5e1] to-[#94a3b8]"></div>

    <x-public-navbar />

    {{-- HERO --}}
    <div class="relative z-10 pt-32 pb-12 overflow-hidden">
        <div class="relative max-w-7xl mx-auto px-6">
            {{-- Botón Mall al inicio --}}
            <div class="flex justify-center mb-6">
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

            <div class="flex flex-col items-center text-center space-y-4">
                <span class="px-4 py-1.5 rounded-full bg-slate-900/10 border border-slate-900/10 text-slate-800 text-[10px] font-black tracking-widest uppercase">
                    Catálogo Exclusivo
                </span>
                <h1 class="text-4xl md:text-6xl font-black tracking-tighter text-slate-900 leading-none">
                    Descubre lo mejor de <br>
                    <span class="text-indigo-700">Nuestras Tiendas</span>
                </h1>
                <p class="max-w-2xl text-slate-600 font-medium text-sm md:text-base">
                    Explora una selección curada de productos de las marcas más prestigiosas presentes en nuestras instalaciones.
                </p>
            </div>
        </div>
    </div>

    {{-- Selector de infraestructura --}}
    <div class="sticky top-[80px] z-40 bg-white/20 backdrop-blur-xl border-y border-white/30 py-4 shadow-lg">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-700 mr-2">Establecimiento:</span>
                <template x-for="infra in infras" :key="infra.id">
                    <button
                        @click="selectedInfraId = infra.id"
                        class="infra-tag whitespace-nowrap px-5 py-2 rounded-2xl text-xs font-black uppercase tracking-wider border transition-all duration-300 cursor-pointer"
                        :class="selectedInfraId === infra.id
                            ? 'bg-slate-900 border-slate-900 text-white shadow-md scale-105'
                            : 'bg-white/70 border-white/50 text-slate-600 hover:border-slate-300 hover:bg-white'"
                        x-text="infra.nombre">
                    </button>
                </template>

                <span class="ml-auto text-xs font-black text-slate-700 uppercase tracking-wider"
                    x-text="filteredProducts.length + ' producto' + (filteredProducts.length === 1 ? '' : 's')"></span>
            </div>
        </div>
    </div>

    {{-- Grid de productos --}}
    <main class="relative z-10 max-w-7xl mx-auto px-6 py-12">
        <div x-show="filteredProducts.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">

            <template x-for="product in filteredProducts" :key="product.id">
                <div class="product-card group glass-hud rounded-[2.5rem] border border-white/30 p-4 flex flex-col h-full relative overflow-hidden">
                    
                    {{-- DECORATIVE SHINE OVERLAY --}}
                    <div class="absolute inset-0 bg-[linear-gradient(110deg,transparent_40%,rgba(255,255,255,0.15)_50%,transparent_60%)] opacity-80 pointer-events-none"></div>

                    <div class="relative aspect-[4/5] rounded-[2rem] overflow-hidden bg-slate-100/50 mb-6 border border-white/20">
                        <img :src="product.imagenes[0]?.url"
                            class="w-full h-full object-cover transition-transform duration-75 group-hover:scale-105">

                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 rounded-full bg-white/90 backdrop-blur text-[10px] font-black text-slate-900 uppercase tracking-wider shadow-sm"
                                x-text="product.categoria"></span>
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    <div class="px-2 space-y-3 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="text-base font-black text-slate-900 tracking-tight leading-tight group-hover:text-indigo-700 transition-colors"
                                x-text="product.nombre"></h3>
                            <span class="text-lg font-black text-slate-900 whitespace-nowrap" x-text="'Bs. ' + Number(product.precio).toFixed(2)"></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 border-2 border-white flex items-center justify-center shadow-inner">
                                    <x-heroicon-s-building-storefront class="w-3 h-3 text-indigo-700" />
                                </div>
                            </div>
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest"
                                x-text="product.tienda_nombre"></span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            type="button"
                            @click="activeProduct = product"
                            class="w-full py-4 rounded-2xl bg-white/60 text-slate-900 hover:bg-slate-900 hover:text-white transition duration-300 font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 shadow-sm border border-white/50 cursor-pointer">
                            Ver Detalles
                            <x-heroicon-o-arrow-right class="w-4 h-4 pointer-events-none" />
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- EMPTY STATE --}}
        <div x-show="filteredProducts.length === 0"
            class="flex flex-col items-center justify-center py-24 text-center space-y-6 max-w-2xl mx-auto glass-hud rounded-[3rem] border border-white/30">
            <div class="w-20 h-20 bg-slate-900/5 rounded-full flex items-center justify-center">
                <x-heroicon-o-shopping-bag class="w-10 h-10 text-slate-500" />
            </div>
            <div class="space-y-2 px-6">
                <h2 class="text-xl font-black text-slate-900">No hay productos</h2>
                <p class="text-slate-600 font-medium text-sm" x-text="($store.search?.q ? 'No se encontraron resultados para «' + $store.search.q + '»' : 'Esta infraestructura aún no tiene productos registrados en sus tiendas.')"></p>
            </div>
            <button x-show="$store.search?.q" @click="$store.search.q = ''"
                class="px-8 py-3.5 bg-slate-900 text-white rounded-2xl font-bold hover:bg-indigo-700 transition duration-300 text-xs uppercase tracking-widest shadow-md cursor-pointer">
                Limpiar búsqueda
            </button>
        </div>
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

    {{-- ============================================================
         MODAL: Ver Detalles del Producto
         ============================================================ --}}
    <template x-teleport="body">
        <div x-show="activeProduct" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-8"
            x-transition.opacity.duration.300ms>

            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-xl" @click="activeProduct = null"></div>

            <div x-show="activeProduct"
                class="relative w-full max-w-4xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl rounded-[3rem] shadow-2xl border border-white/40 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh]"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <button
                    type="button"
                    @click.stop="activeProduct = null"
                    class="absolute top-4 right-4 md:top-8 md:right-8 z-20 p-2 rounded-full bg-white/20 backdrop-blur hover:bg-white/40 transition cursor-pointer"
                    title="Cerrar">
                    <svg class="w-5 h-5 md:w-6 md:h-6 pointer-events-none text-slate-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="grid grid-cols-1 md:grid-cols-2 overflow-y-auto">
                    <div class="bg-slate-100/50 aspect-square md:aspect-auto md:min-h-[400px] relative border-r border-slate-900/10">
                        <img :src="activeProduct?.imagenes?.[0]?.url" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 rounded-full bg-white/90 backdrop-blur text-[10px] font-black text-slate-900 uppercase tracking-wider shadow-sm"
                                x-text="activeProduct?.categoria"></span>
                        </div>
                    </div>

                    <div class="p-6 md:p-10 flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-black tracking-[0.3em] uppercase text-slate-400 mb-2"
                                x-text="(activeProduct?.marca ? activeProduct.marca + ' · ' : '') + (activeProduct?.tienda_nombre || '')"></div>
                            <h2 class="text-2xl md:text-3xl font-black tracking-tight text-slate-900"
                                x-text="activeProduct?.nombre"></h2>

                            <div class="mt-3 text-3xl font-black text-indigo-700"
                                x-text="'Bs. ' + Number(activeProduct?.precio ?? 0).toFixed(2)"></div>

                            <p class="mt-6 text-slate-600 leading-relaxed text-sm md:text-base"
                                x-text="activeProduct?.descripcion || 'Este producto aún no tiene descripción.'"></p>

                            <div class="mt-8 grid grid-cols-2 gap-3 text-xs">
                                <div class="rounded-xl border border-slate-900/10 p-3 bg-slate-900/5">
                                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Tienda</div>
                                    <div class="font-bold text-slate-900 mt-1 truncate" x-text="activeProduct?.tienda_nombre || '—'"></div>
                                </div>
                                <div class="rounded-xl border border-slate-900/10 p-3 bg-slate-900/5">
                                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Local</div>
                                    <div class="font-bold text-slate-900 mt-1 truncate" x-text="activeProduct?.tienda_numero || '—'"></div>
                                </div>
                                <div class="rounded-xl border border-slate-900/10 p-3 bg-slate-900/5">
                                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Piso</div>
                                    <div class="font-bold text-slate-900 mt-1 truncate" x-text="(activeProduct?.piso_numero && activeProduct?.piso_nombre && activeProduct?.piso_numero !== activeProduct?.piso_nombre ? activeProduct.piso_numero + ' (' + activeProduct.piso_nombre + ')' : (activeProduct?.piso_numero || activeProduct?.piso_nombre)) || '—'"></div>
                                </div>
                                <div class="rounded-xl border border-slate-900/10 p-3 bg-slate-900/5">
                                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Marca</div>
                                    <div class="font-bold text-slate-900 mt-1 truncate" x-text="activeProduct?.marca || '—'"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8">
                            <a :href="'/?infraestructura_id=' + selectedInfraId + '&tienda_id=' + activeProduct.tienda_id"
                                class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-indigo-700 hover:text-indigo-800 transition duration-300">
                                Ver la tienda en el mall
                                <x-heroicon-o-arrow-right class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</body>
</html>
