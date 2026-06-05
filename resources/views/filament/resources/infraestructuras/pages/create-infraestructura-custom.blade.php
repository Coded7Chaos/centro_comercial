<x-filament-panels::page>
    <div x-data="{
        map: null,
        marker: null,
        lat: @entangle('lat'),
        lng: @entangle('long'),
        address: @entangle('ubicacion'),
        
        initMap() {
            this.map = L.map('map-container').setView([this.lat, this.lng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);

            this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);

            this.map.on('click', (e) => {
                this.updatePosition(e.latlng.lat, e.latlng.lng);
            });

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.updatePosition(pos.lat, pos.lng);
            });
        },

        async updatePosition(lat, lng) {
            this.lat = lat;
            this.lng = lng;
            this.marker.setLatLng([lat, lng]);
            
            // Reverse Geocoding
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                if (data.display_name) {
                    this.address = data.display_name;
                }
            } catch (error) {
                console.error('Error in reverse geocoding:', error);
            }
        }
    }" x-init="initMap()" class="space-y-6">
        
        {{-- SECCIÓN: DATOS BÁSICOS Y MAPA --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="nombre" placeholder="Ej. Centro Comercial Los Pinos"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3">
                @error('nombre') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                    Ubicación en el mapa <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 text-pretty">Haz clic en el mapa para marcar la ubicación de la infraestructura.</p>
                
                <div id="map-container" class="w-full h-80 rounded-2xl border border-gray-200 dark:border-gray-700 z-0" wire:ignore></div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Dirección <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="ubicacion" 
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3">
                <p class="text-[10px] text-gray-400 mt-1">La dirección se completa automáticamente según el marcador, pero puedes editarla.</p>
                @error('ubicacion') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- SECCIÓN: PISOS Y TIENDAS --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between px-2">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pisos</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Añade los pisos que componen esta infraestructura y administra las tiendas de cada uno.</p>
                </div>
                <button type="button" wire:click="addPiso" 
                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold rounded-xl shadow-sm transition">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Añadir piso
                </button>
            </div>

            @foreach($pisos as $pIndex => $piso)
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-3xl overflow-hidden shadow-sm" wire:key="piso-{{ $pIndex }}">
                    <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="text-gray-400 flex-shrink-0">
                                <x-heroicon-m-bars-3 class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3 w-full mr-4">
                                <div class="flex items-center gap-2 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 bg-gray-50/50 dark:bg-gray-900/50">
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider flex-shrink-0">Nivel/Número:</span>
                                    <input type="text" wire:model="pisos.{{ $pIndex }}.numero" 
                                        class="font-extrabold text-gray-800 dark:text-gray-200 text-xs border-none bg-transparent focus:ring-0 p-0 w-28 placeholder-gray-400"
                                        placeholder="Ej. Planta baja">
                                </div>
                                <div class="flex items-center gap-2 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 bg-gray-50/50 dark:bg-gray-900/50 flex-1">
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider flex-shrink-0">Nombre:</span>
                                    <input type="text" wire:model="pisos.{{ $pIndex }}.nombre" 
                                        class="font-extrabold text-gray-800 dark:text-gray-200 text-xs border-none bg-transparent focus:ring-0 p-0 w-full placeholder-gray-400"
                                        placeholder="Ej. Sky Lounge">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900 rounded-xl px-2.5 py-2 border border-gray-200 dark:border-gray-700">
                                <button type="button" wire:click="openBackgroundModal({{ $pIndex }})"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 rounded-lg hover:border-primary-300 hover:text-primary-600 transition">
                                    <x-heroicon-o-photo class="w-4 h-4" />
                                    Elegir imagen de fondo
                                </button>
                                <div class="w-16 h-10 rounded-lg border border-gray-300 dark:border-gray-600 bg-cover bg-center transition-all duration-300 shadow-sm"
                                     style="background-image: url('{{ $piso['imagen_fondo'] ?? '/images/backgrounds/bg_mall_white.jpg' }}')">
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Estado:</span>
                                <select wire:model="pisos.{{ $pIndex }}.estado" 
                                    class="text-xs font-bold border-none bg-gray-50 dark:bg-gray-900 rounded-lg p-1.5 focus:ring-0 cursor-pointer
                                    @if($piso['estado'] == 'activo') text-emerald-600 @else text-red-800 @endif">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                            
                            <button type="button" wire:click="removePiso({{ $pIndex }})" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Tiendas</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Haz clic en 'Añadir tienda' para registrar una nueva tienda en este piso.</p>
                            </div>
                            
                        </div>

                        <div class="space-y-4">
                            @foreach($piso['tiendas'] as $tIndex => $tienda)
                                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-6 shadow-sm relative group" wire:key="piso-{{ $pIndex }}-tienda-{{ $tIndex }}">
                                    <div class="absolute -left-3 top-1/2 -translate-y-1/2 text-gray-200 group-hover:text-gray-300 transition">
                                        <x-heroicon-m-bars-3 class="w-6 h-6" />
                                    </div>

                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-gray-800 dark:text-gray-200 text-xs uppercase">Tienda</h4>
                                            <input type="text" wire:model="pisos.{{ $pIndex }}.tiendas.{{ $tIndex }}.numero"
                                                class="font-bold text-primary-600 text-xs border-none bg-gray-50 dark:bg-gray-900 rounded p-1 w-16 focus:ring-0"
                                                placeholder="001">
                                        </div>
                                        
                                        <div class="flex-1 flex justify-center">
                                            <input type="text" wire:model="pisos.{{ $pIndex }}.tiendas.{{ $tIndex }}.nombre"
                                                class="text-lg font-extrabold text-gray-900 dark:text-white border-none bg-transparent focus:ring-0 p-0 text-center w-full max-w-xs"
                                                placeholder="NOMBRE DE LA TIENDA">
                                        </div>

                                        <button type="button" wire:click="removeTienda({{ $pIndex }}, {{ $tIndex }})" 
                                            class="text-red-400 hover:text-red-600 transition">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Teléfono de referencia <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="pisos.{{ $pIndex }}.tiendas.{{ $tIndex }}.telefono_referencia"
                                                class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-xl text-sm px-4 py-2.5 focus:ring-primary-500"
                                                placeholder="Ej. +591 ...">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Tamaño (m²) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="pisos.{{ $pIndex }}.tiendas.{{ $tIndex }}.tamano"
                                                class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-xl text-sm px-4 py-2.5 focus:ring-primary-500"
                                                placeholder="0.00">
                                        </div>
                                        <div class="md:col-span-8">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Descripción</label>
                                            <textarea wire:model="pisos.{{ $pIndex }}.tiendas.{{ $tIndex }}.descripcion" rows="1"
                                                class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-xl text-sm px-4 py-2.5 focus:ring-primary-500"
                                                placeholder="Breve descripción..."></textarea>
                                        </div>
                                        </div>

                                        </div>
                                        @endforeach
                            
                            <button type="button" wire:click="addTienda({{ $pIndex }})" 
                                class="w-full py-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl text-gray-400 hover:text-primary-500 hover:border-primary-200 transition text-sm font-bold flex items-center justify-center gap-2">
                                <x-heroicon-o-plus class="w-5 h-5" />
                                Añadir tienda
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- FOOTER DE ACCIONES --}}
        <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-800">
            <a href="{{ \App\Filament\Resources\Infraestructuras\InfraestructurasResource::getUrl('index') }}" 
                class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="button" wire:click="save" 
                class="inline-flex items-center px-8 py-2.5 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-xl shadow-lg transition gap-2">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                Guardar infraestructura
            </button>
        </div>
    </div>

    @if($backgroundModalOpen)
        <div
            class="fixed inset-0 flex items-center justify-center px-4 py-6"
            style="z-index: 2147483647; background: rgba(2, 6, 23, 0.88); backdrop-filter: blur(30px) saturate(55%); -webkit-backdrop-filter: blur(30px) saturate(55%);"
            wire:key="background-modal"
        >
            <div class="w-full max-w-6xl max-h-[92vh] overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Imagen de fondo del piso</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Selecciona una opción existente o sube una nueva imagen.</p>
                    </div>
                    <button type="button" wire:click="closeBackgroundModal"
                        class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 max-h-[calc(92vh-74px)] overflow-y-auto">
                    <div class="lg:col-span-8 bg-gray-100 dark:bg-gray-950 p-6">
                        <div class="h-[56vh] min-h-80 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex items-center justify-center overflow-hidden">
                            <img src="{{ $this->backgroundModalPreviewUrl }}"
                                alt="Previsualización de imagen de fondo"
                                class="h-full w-full object-cover">
                        </div>
                    </div>

                    <div class="lg:col-span-4 p-6 space-y-6">
                        <div>
                            <h4 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Opciones disponibles</h4>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($this->backgroundOptions as $option)
                                    <button type="button" wire:click="selectBackground('{{ $option['url'] }}')"
                                        class="group overflow-hidden rounded-xl border text-left transition {{ $backgroundPreview === $option['url'] && !$backgroundUpload ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-primary-300' }}">
                                        <div class="aspect-video bg-cover bg-center" style="background-image: url('{{ $option['url'] }}')"></div>
                                        <div class="px-3 py-2 bg-white dark:bg-gray-900">
                                            <span class="block text-xs font-bold text-gray-700 dark:text-gray-200 truncate">{{ $option['label'] }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Añadir nueva imagen de fondo</h4>
                            <label class="relative flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 px-4 py-6 text-center hover:border-primary-400 hover:bg-primary-50/40 dark:hover:bg-primary-950/20 transition">
                                <x-heroicon-o-arrow-up-tray class="w-8 h-8 text-gray-400 mb-3" />
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Añadir nueva imagen de fondo</span>
                                <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Puede hacer click para añadir una nueva imagen o arrastrar una en esta sección.</span>
                                <input type="file" wire:model="backgroundUpload" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                            </label>
                            <div wire:loading wire:target="backgroundUpload" class="mt-2 text-xs font-bold text-primary-600">Cargando imagen...</div>
                            @error('backgroundUpload') <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                            @error('backgroundSelection') <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-800">
                            <button type="button" wire:click="closeBackgroundModal"
                                class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancelar
                            </button>
                            <button type="button" wire:click="confirmBackgroundImage" wire:loading.attr="disabled" wire:target="confirmBackgroundImage,backgroundUpload"
                                @disabled(!$backgroundSelectionMade && !$backgroundUpload)
                                class="px-4 py-2.5 bg-primary-600 hover:bg-primary-500 disabled:bg-gray-300 disabled:text-gray-500 disabled:opacity-100 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Confirmar imagen de fondo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Assets de Leaflet --}}
    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpush
</x-filament-panels::page>
