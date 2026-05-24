<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Lista de Infraestructuras</h1>
            <a href="{{ \App\Filament\Resources\Infraestructuras\InfraestructurasResource::getUrl('create') }}" 
                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold rounded-xl shadow-sm transition">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Nueva infraestructura
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @forelse($this->getInfraestructuras() as $infra)
                @php
                    $tiendas = $infra->pisosInfraestructura->flatMap->tiendas;
                    $disponibles = $tiendas->where('estado.estado', 'Disponible')->count();
                    $ocupadas = $tiendas->where('estado.estado', 'Alquilada')->count();
                    $otros = $tiendas->whereNotIn('estado.estado', ['disponible', 'alquilado'])->count();
                @endphp
                
                <div x-data="{ 
                        expanded: false,
                        map: null,
                        initMap() {
                            if (this.expanded && !this.map) {
                                setTimeout(() => {
                                    this.map = L.map('map-{{ $infra->id }}').setView([{{ $infra->lat }}, {{ $infra->long }}], 15);
                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
                                    L.marker([{{ $infra->lat }}, {{ $infra->long }}]).addTo(this.map);
                                }, 100);
                            }
                        }
                    }" 
                    x-init="$watch('expanded', value => initMap())"
                    class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden transition-all duration-300">
                    
                    {{-- HEADER COLAPSADO --}}
                    <div class="p-6 cursor-pointer" @click="expanded = !expanded">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl text-indigo-600 dark:text-indigo-400">
                                    <x-heroicon-o-building-office-2 class="w-8 h-8" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $infra->nombre }}</h3>
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                            <x-heroicon-o-map-pin class="w-4 h-4" />
                                            {{ Str::limit($infra->ubicacion, 60) }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $infra->pisos }} pisos      -       {{ $tiendas->count() }} tiendas en total
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="hidden lg:flex flex-col items-end gap-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Detalle de tiendas:</span>
                                    <div class="flex items-center gap-2">
                                        <div class="px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 shadow-sm">
                                            {{ $disponibles }} Libres
                                        </div>
                                        <div class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-xs font-bold text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 shadow-sm">
                                            {{ $ocupadas }} Alquiladas
                                        </div>
                                        @php
                                            $reservadas = $tiendas->where('estado', 'reservado')->count();
                                            $mantenimiento = $tiendas->where('estado', 'mantenimiento')->count();
                                        @endphp
                                        @if($reservadas > 0)
                                            <div class="px-3 py-1 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-xs font-bold text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50 shadow-sm">
                                                {{ $reservadas }} Reservadas
                                            </div>
                                        @endif
                                        @if($mantenimiento > 0)
                                            <div class="px-3 py-1 bg-purple-50 dark:bg-purple-900/20 rounded-xl text-xs font-bold text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-800/50 shadow-sm">
                                                {{ $mantenimiento }} Mantenimiento
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <button type="button" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition" :class="expanded ? 'rotate-180' : ''">
                                    <x-heroicon-o-chevron-down class="w-6 h-6 text-gray-400" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- CUERPO EXPANDIDO (SOLO LECTURA) --}}
                    <div x-show="expanded" x-collapse>
                        <div class="p-8 border-t border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-950/20 space-y-8">
                            
                            {{-- MAPA --}}
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Ubicación Geográfica</h4>
                                <div id="map-{{ $infra->id }}" class="w-full h-64 rounded-2xl border border-gray-200 dark:border-gray-700 z-0"></div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                    {{ $infra->ubicacion }}
                                </p>
                            </div>

                            {{-- PISOS Y TIENDAS --}}
                            <div class="space-y-6">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Distribución de Plantas</h4>
                                
                                @foreach($infra->pisosInfraestructura as $piso)
                                    <div class="rounded-2xl border overflow-hidden shadow-sm transition-colors
                                        @if($piso->estado == 'inactivo') bg-red-50/40 dark:bg-red-900/10 border-red-100 dark:border-red-900/30 @else bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 @endif">
                                        @if($piso->estado == 'inactivo')
                                        <div class="px-5 py-3 bg-red-300 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                        @else
                                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                        @endif
                                            <div class="flex items-center gap-3">
                                                <span class="font-bold text-gray-700 dark:text-gray-300 text-sm">{{ $piso->nombre }}</span>
                                                @if($piso->estado == 'inactivo')
                                                    <span class="px-2 py-0.5 rounded-lg bg-red-200 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-[9px] font-extrabold uppercase tracking-wider border border-red-200 dark:border-red-800">Inactivo</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[9px] font-extrabold uppercase tracking-wider border border-emerald-200 dark:border-emerald-800">Activo</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-bold @if($piso->estado == 'activo') text-gray-400 uppercase" @else text-[#ffffff] uppercase" @endif>{{ $piso->tiendas->count() }} @if($piso->tiendas->count() > 1) TIENDAS @else TIENDA @endif</span>
                                        </div>
                                        <div class="p-4 flex flex-wrap gap-4">
                                            @foreach($piso->tiendas as $tienda)
                                                <div x-data="{ expandedTienda: false }" 
                                                    wire:key="tienda-{{ $tienda->id }}-{{ $loop->index }}"
                                                    class="rounded-xl border transition-all duration-300 shadow-sm overflow-hidden flex flex-col md:flex-row
                                                    @if($piso->estado == 'inactivo') border-red-400 dark:border-red-900/30 bg-red-50/20 dark:bg-red-950/10 @else border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 @endif
                                                    w-full sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.33%-0.7rem)]"
                                                    :class="expandedTienda ? 'lg:w-[calc(66.66%-0.7rem)]' : ''">
                                                    
                                                    {{-- PARTE IZQUIERDA: INFO BÁSICA --}}
                                                    <div class="p-4 flex-1 flex flex-col justify-between min-w-[200px]">
                                                        <div>
                                                            <div class="flex items-center justify-between mb-3">
                                                                <span class="text-xs font-bold text-primary-600 dark:text-primary-400">Tienda {{ $tienda->numero }}</span>
                                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase
                                                                    @if($tienda->estado->estado == 'Disponible') bg-emerald-100 text-emerald-700 @elseif($tienda->estado->estado == 'Alquilada') bg-blue-100 text-blue-700 @else bg-amber-100 text-amber-700 @endif">
                                                                    {{ $tienda->estado->estado }}
                                                                </span>
                                                            </div>

                                                            <div class="space-y-1.5">
                                                                <p class="text-[11px] text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                                    <x-heroicon-o-phone class="w-3.5 h-3.5" />
                                                                    {{ $tienda->telefono_referencia ?: 'Sin teléfono' }}
                                                                </p>
                                                                <p class="text-[11px] text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                                    <x-heroicon-o-arrows-pointing-out class="w-3.5 h-3.5" />
                                                                    {{ $tienda->tamano ?: '0' }} m²
                                                                </p>
                                                                <p class="text-[11px] text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                                    <x-heroicon-s-user class="w-3.5 h-3.5 text-gray-400" />
                                                                    <span class="truncate">{{ $tienda->cliente && $tienda->cliente->user ? ($tienda->cliente->user->nombres . ' ' . $tienda->cliente->user->apellido_paterno) : 'Sin dueño' }}</span>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- BOTÓN EXPANDIR --}}
                                                        <div class="mt-4 flex justify-end">
                                                            <button type="button" @click="expandedTienda = !expandedTienda" 
                                                                class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-primary-500 transition-all duration-300"
                                                                :class="expandedTienda ? 'rotate-180 text-primary-500 border-primary-200' : ''">
                                                                <x-heroicon-o-chevron-right class="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- PARTE DERECHA: INFO DETALLADA (Horizontal) --}}
                                                    <div x-show="expandedTienda" 
                                                        x-transition:enter="transition ease-out duration-300"
                                                        x-transition:enter-start="opacity-0 transform -translate-x-4"
                                                        x-transition:enter-end="opacity-100 transform translate-x-0"
                                                        class="flex-1 p-4 bg-white/50 dark:bg-gray-900/40 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-800 flex flex-col gap-3 min-w-[250px]">
                                                        
                                                        <div>
                                                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Nombre comercial:</p>
                                                            <p class="text-xs font-extrabold text-gray-800 dark:text-white leading-tight uppercase">
                                                                {{ $tienda->nombre ?: 'Sin nombre' }}
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Marcas asociadas:</p>
                                                            <div class="flex flex-wrap gap-1">
                                                                @forelse($tienda->marcas as $marca)
                                                                    <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[9px] font-bold rounded-md border border-indigo-100 dark:border-indigo-800/50">
                                                                        {{ $marca->nombre }}
                                                                    </span>
                                                                @empty
                                                                    <span class="text-[10px] text-gray-400 italic font-medium">Ninguna marca</span>
                                                                @endforelse
                                                            </div>
                                                        </div>

                                                        <div class="flex-1">
                                                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Descripción técnica:</p>
                                                            <div class="max-h-20 overflow-y-auto pr-2 text-[10px] text-gray-500 dark:text-gray-400 italic leading-relaxed scrollbar-thin">
                                                                {{ $tienda->descripcion ?: 'Sin descripción detallada.' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- FOOTER DE TARJETA --}}
                            <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-800">
                                <button type="button" 
                                    wire:click="deleteInfraestructura({{ $infra->id }})"
                                    wire:confirm="¿Estás seguro de eliminar esta infraestructura? Se borrarán todos sus pisos y tiendas asociadas."
                                    class="text-red-500 hover:text-red-700 text-sm font-bold flex items-center gap-2 transition">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                    Eliminar Infraestructura
                                </button>

                                <a href="{{ \App\Filament\Resources\Infraestructuras\InfraestructurasResource::getUrl('edit', ['record' => $infra]) }}"
                                    class="inline-flex items-center px-6 py-2.5 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-xl shadow-lg transition gap-2">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    Editar Infraestructura
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-gray-50 dark:bg-gray-800/20 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                    <p class="text-gray-500 dark:text-gray-400 italic">No hay infraestructuras registradas aún.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Assets de Leaflet --}}
    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    @endpush
</x-filament-panels::page>