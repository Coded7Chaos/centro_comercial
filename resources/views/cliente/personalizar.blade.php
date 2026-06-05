<x-layouts.client title="Personalización de Inicio">

    <div class="max-w-6xl mx-auto"
         x-data="{
             activeStore: false,
             productModalOpen: false,
             vitrinaPosition: null,
             
             // Dynamic Category/Subcategory state
             categories: @js($categorias),
             selectedCategory: '',
             subcategories: [],
             selectedSubcategory: '',
             
             init() {
                 this.$watch('selectedCategory', val => {
                     const cat = this.categories.find(c => c.id == val);
                     this.subcategories = cat ? cat.subcategorias : [];
                     this.selectedSubcategory = '';
                 });
             },
             
             openCreateModal(position) {
                 this.vitrinaPosition = position;
                 this.selectedCategory = '';
                 this.subcategories = [];
                 this.selectedSubcategory = '';
                 if (this.categories.length > 0) {
                     this.selectedCategory = this.categories[0].id;
                 }
                 this.productModalOpen = true;
             },

             // Modal de confirmación de eliminación
             deleteModal: false,
             deleteName: '',
             deleteUrl: '',
             openDeleteModal(name, url) {
                 this.deleteName = name;
                 this.deleteUrl  = url;
                 this.deleteModal = true;
             }
         }">

        {{-- TOAST éxito --}}
        @if(session('success'))
            <div x-data="{ visible: true }"
                 x-show="visible"
                 x-init="setTimeout(() => visible = false, 4000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-emerald-600 text-white px-5 py-4 rounded-2xl shadow-xl shadow-emerald-600/30 text-sm font-semibold max-w-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
                <button @click="visible = false" class="ml-2 opacity-70 hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif

        {{-- TOAST error --}}
        @if(session('error'))
            <div x-data="{ visible: true }"
                 x-show="visible"
                 x-init="setTimeout(() => visible = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-red-600 text-white px-5 py-4 rounded-2xl shadow-xl shadow-red-600/30 text-sm font-semibold max-w-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
                <button @click="visible = false" class="ml-2 opacity-70 hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif

        {{-- MODAL confirmación eliminar producto --}}
        <div x-show="deleteModal" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="deleteModal = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="deleteModal = false"></div>
            <div x-show="deleteModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 space-y-5">
                <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-2xl mx-auto">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="text-center space-y-1">
                    <h3 class="text-lg font-black text-slate-800">¿Eliminar producto?</h3>
                    <p class="text-sm text-slate-500">Estás a punto de eliminar <strong class="text-slate-700" x-text="'«' + deleteName + '»'"></strong>. Esta acción no se puede deshacer.</p>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="deleteModal = false"
                            class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <form :action="deleteUrl" method="POST" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition shadow-sm shadow-red-600/30">
                            Sí, eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if($tiendas->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center text-slate-500">
                Aún no tienes locales asignados por el administrador.
            </div>
        @else
            @php
                $currentBrand = $tienda->marcas->firstWhere('cliente_id', $cliente->id) ?? $tienda->marcas->first();
                $marcaLogoUrl = $currentBrand && $currentBrand->logo ? (str_starts_with($currentBrand->logo, 'http') ? $currentBrand->logo : Storage::url($currentBrand->logo)) : null;
                $marcaNombre = $currentBrand ? $currentBrand->nombre : ($tienda->nombre ?: 'Mi Tienda');
            @endphp

            <!-- Canvas Container (Fondo del Piso + Card Centrado como welcome.blade.php) -->
            <div class="relative w-full rounded-[2.5rem] border border-slate-200/80 shadow-2xl overflow-hidden py-16 px-4 md:px-8 flex items-center justify-center bg-slate-800"
                 style="background-image: url('{{ $tienda->piso->imagen_fondo ?: '/images/backgrounds/bg_mall_white.jpg' }}'); background-size: cover; background-position: center; min-height: 700px;">
                
                {{-- PANORAMA AMBIENT GRADIENTS --}}
                <div class="absolute inset-0 bg-gradient-to-b from-white/20 via-transparent to-slate-900/35 z-0"></div>
                <div class="absolute inset-0 z-0" style="background: radial-gradient(ellipse at 50% 55%, transparent 40%, rgba(15,23,42,0.4) 100%)"></div>

                {{-- TOP CEILING SHADOW --}}
                <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-slate-900/40 via-slate-900/10 to-transparent z-10"></div>

                {{-- FLOOR MARBLE REFLECTION --}}
                <div class="absolute inset-x-0 bottom-0 h-1/4 z-10" 
                    style="background: linear-gradient(to top, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.05) 50%, transparent 100%); backdrop-filter: blur(1.5px);"></div>

                {{-- FLOATING HUD - LEFT: Store Selector if has multiple --}}
                @if($tiendas->count() > 1)
                    <div class="absolute top-6 left-6 z-20 pointer-events-auto">
                        <div class="flex items-center gap-2 p-1.5 rounded-2xl border border-white/40 bg-slate-900/60 backdrop-blur-2xl shadow-xl">
                            <span class="text-white/80 text-[9px] font-bold tracking-[0.2em] uppercase pl-3">Local:</span>
                            <select onchange="window.location.search = '?tienda_id=' + this.value" 
                                    class="rounded-xl border-0 bg-transparent text-xs py-1.5 pl-2 pr-8 focus:ring-0 font-bold text-white cursor-pointer">
                                @foreach($tiendas as $t)
                                    <option value="{{ $t->id }}" {{ $t->id === $tienda->id ? 'selected' : '' }} class="text-slate-800 font-bold">
                                        N° {{ $t->numero }} — {{ $t->nombre ?: 'Sin Nombre Comercial' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                {{-- FLOATING HUD - RIGHT: Floor Details Badge --}}
                <div class="absolute top-6 right-6 z-20 pointer-events-none flex items-center gap-3">
                    <div class="flex items-center gap-3 rounded-2xl border border-white/40 bg-slate-900/60 px-4 py-2.5 backdrop-blur-2xl shadow-xl">
                        <div class="text-left">
                            <div class="text-white/80 text-[8px] tracking-[0.3em] uppercase">PISO {{ $tienda->piso->displayLevel }} · LOCAL {{ $tienda->numero }}</div>
                            <div class="text-white text-xs font-black tracking-tight mt-0.5">{{ $tienda->piso->name }}</div>
                        </div>
                    </div>
                </div>

                {{-- CARD DE TIENDA --}}
                <div class="relative w-full max-w-[430px] min-h-[500px] md:min-h-[530px] flex flex-col z-10 transition-all duration-300">
                    
                    {{-- AWNING --}}
                    <div class="relative h-20 md:h-24 rounded-t-[20px] px-4 md:px-6 flex items-center justify-between gap-4 overflow-hidden border-t border-x border-white/50 bg-gradient-to-b from-slate-600 to-slate-700 shadow-md">
                        <div class="absolute inset-0 bg-[linear-gradient(110deg,transparent_30%,rgba(255,255,255,0.4)_45%,transparent_60%)] opacity-70 pointer-events-none"></div>
                        <div class="relative flex items-center gap-3 md:gap-4 flex-1 min-w-0">
                            
                            {{-- Brand Logo Editor --}}
                            <div class="relative group/logo flex h-10 w-10 md:h-14 md:w-14 items-center justify-center rounded-xl border border-white/60 bg-white/30 backdrop-blur shadow-inner text-sm md:text-xl font-black text-white uppercase overflow-hidden shrink-0">
                                @if($marcaLogoUrl)
                                    <img src="{{ $marcaLogoUrl }}" class="h-full w-full object-cover">
                                @else
                                    <span>{{ substr($marcaNombre, 0, 2) }}</span>
                                @endif

                                @if($currentBrand)
                                    <button type="button" onclick="document.getElementById('input-brand-logo-card').click()"
                                            class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover/logo:opacity-100 transition cursor-pointer"
                                            title="Cambiar Logo de Marca">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form id="form-brand-logo-card" method="POST" action="{{ route('cliente.personalizar.marca.logo.update') }}" enctype="multipart/form-data" class="hidden">
                                        @csrf
                                        <input type="hidden" name="marca_id" value="{{ $currentBrand->id }}">
                                        <input type="file" id="input-brand-logo-card" name="logo" onchange="this.form.submit()">
                                    </form>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1 space-y-0.5">
                                {{-- Store Name Input --}}
                                <form method="POST" action="{{ route('cliente.personalizar.tienda.update') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                    <input type="hidden" name="telefono_referencia" value="{{ $tienda->telefono_referencia }}">
                                    <input type="hidden" name="descripcion" value="{{ $tienda->descripcion }}">
                                    <input type="hidden" name="marca_id" value="{{ $currentBrand ? $currentBrand->id : '' }}">

                                    <div class="relative group/name flex items-center">
                                        <input type="text" name="nombre" value="{{ $tienda->nombre }}" 
                                               class="bg-transparent border-0 border-b border-dashed border-transparent hover:border-white/50 focus:border-white focus:ring-0 text-base md:text-xl font-black text-white p-0 tracking-tight w-full outline-none rounded-none"
                                               title="Presione Enter para guardar">
                                        <span class="absolute right-1 opacity-0 group-hover/name:opacity-100 text-white/70 pointer-events-none transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </span>
                                    </div>
                                </form>

                                <div class="flex items-center gap-2">
                                    <span class="text-[8px] md:text-[9px] tracking-[0.2em] font-bold opacity-80 uppercase text-white">Local {{ $tienda->numero }}</span>
                                    <span class="text-[8px] font-black tracking-[0.2em] uppercase px-1.5 py-0.5 rounded border border-indigo-400 bg-indigo-500/30 text-white whitespace-nowrap">Alquilada</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative flex-1 overflow-hidden border-x border-b border-white/60 bg-gradient-to-b from-slate-500/80 via-white/50 to-slate-300/60 flex shadow-inner">
                        
                        {{-- Left Window Pane (Vitrina 1 y 2) --}}
                        <div class="relative flex-1 border-r border-white/30 bg-gradient-to-br from-white/50 via-slate-200/40 to-slate-300/50 p-3 flex flex-col gap-3">
                            
                            <!-- Slot 1 -->
                            <div class="relative flex-1 group overflow-hidden rounded-xl border border-white/40 bg-black/10 shadow-inner flex items-center justify-center min-h-[110px]">
                                @if($tienda->vitrina_1)
                                    <img src="{{ str_starts_with($tienda->vitrina_1, 'http') ? $tienda->vitrina_1 : Storage::url($tienda->vitrina_1) }}" class="absolute inset-0 h-full w-full object-cover">
                                    
                                    <div class="absolute inset-0 bg-black/45 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-150">
                                        <button type="button" onclick="document.getElementById('file-vitrina-slot-1').click()"
                                                class="p-2 bg-white text-slate-900 rounded-full shadow hover:scale-110 transition flex items-center justify-center cursor-pointer"
                                                title="Cambiar Imagen">
                                            <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('cliente.personalizar.vitrina.update') }}" enctype="multipart/form-data" class="hidden">
                                        @csrf
                                        <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                        <input type="hidden" name="slot" value="1">
                                        <input type="file" id="file-vitrina-slot-1" name="imagen" onchange="this.form.submit()">
                                    </form>
                                @else
                                    <div class="text-center p-2 flex flex-col items-center justify-center">
                                        <button onclick="document.getElementById('file-vitrina-slot-1-empty').click()" type="button" class="p-2 bg-indigo-600/90 hover:bg-indigo-700 text-white rounded-full shadow-lg transition hover:scale-110 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                        <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mt-1">Añadir Vitrina 1</span>
                                        <form method="POST" action="{{ route('cliente.personalizar.vitrina.update') }}" enctype="multipart/form-data" class="hidden">
                                            @csrf
                                            <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                            <input type="hidden" name="slot" value="1">
                                            <input type="file" id="file-vitrina-slot-1-empty" name="imagen" onchange="this.form.submit()">
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <!-- Slot 2 -->
                            <div class="relative flex-1 group overflow-hidden rounded-xl border border-white/40 bg-black/10 shadow-inner flex items-center justify-center min-h-[110px]">
                                @if($tienda->vitrina_2)
                                    <img src="{{ str_starts_with($tienda->vitrina_2, 'http') ? $tienda->vitrina_2 : Storage::url($tienda->vitrina_2) }}" class="absolute inset-0 h-full w-full object-cover">
                                    
                                    <div class="absolute inset-0 bg-black/45 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-150">
                                        <button type="button" onclick="document.getElementById('file-vitrina-slot-2').click()"
                                                class="p-2 bg-white text-slate-900 rounded-full shadow hover:scale-110 transition flex items-center justify-center cursor-pointer"
                                                title="Cambiar Imagen">
                                            <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('cliente.personalizar.vitrina.update') }}" enctype="multipart/form-data" class="hidden">
                                        @csrf
                                        <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                        <input type="hidden" name="slot" value="2">
                                        <input type="file" id="file-vitrina-slot-2" name="imagen" onchange="this.form.submit()">
                                    </form>
                                @else
                                    <div class="text-center p-2 flex flex-col items-center justify-center">
                                        <button onclick="document.getElementById('file-vitrina-slot-2-empty').click()" type="button" class="p-2 bg-indigo-600/90 hover:bg-indigo-700 text-white rounded-full shadow-lg transition hover:scale-110 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                        <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mt-1">Añadir Vitrina 2</span>
                                        <form method="POST" action="{{ route('cliente.personalizar.vitrina.update') }}" enctype="multipart/form-data" class="hidden">
                                            @csrf
                                            <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                            <input type="hidden" name="slot" value="2">
                                            <input type="file" id="file-vitrina-slot-2-empty" name="imagen" onchange="this.form.submit()">
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Central Entrance Door --}}
                        <div class="relative flex w-[100px] md:w-[120px] flex-col items-center justify-center border-x border-white/35 bg-gradient-to-b from-white/50 via-slate-100/60 to-slate-300/70 p-3 cursor-pointer group/door"
                             @click="activeStore = true">
                            <div class="absolute inset-x-2 top-4 bottom-6 rounded-t-lg border border-white/50 bg-gradient-to-b from-white/70 via-white/50 to-white/60 backdrop-blur-md shadow-inner flex flex-col items-center justify-center p-1 text-center select-none transition group-hover/door:from-white/80 group-hover/door:to-white/75">
                                <div class="text-slate-400 text-[8px] font-bold tracking-[0.3em] mb-1">LOCAL</div>
                                <div class="text-slate-800 text-xs md:text-sm font-extrabold">{{ $tienda->numero }}</div>
                                
                                <div class="mt-4 px-2 py-1 rounded bg-slate-900/10 group-hover/door:bg-indigo-600/20 text-indigo-700 text-[7px] font-black uppercase tracking-wider transition">
                                    Detalles
                                </div>
                            </div>
                            <div class="absolute right-2.5 top-1/2 h-8 w-1.5 -translate-y-1/2 rounded-full bg-gradient-to-b from-slate-200 via-slate-400 to-slate-500 shadow"></div>
                        </div>

                        {{-- Right Window Pane (Vitrina 3) --}}
                        <div class="relative flex-1 border-l border-white/30 bg-gradient-to-bl from-white/50 via-slate-200/40 to-slate-300/50 p-3 flex flex-col">
                            <div class="relative flex-1 group overflow-hidden rounded-xl border border-white/40 bg-black/10 shadow-inner flex items-center justify-center min-h-[220px]">
                                @if($tienda->vitrina_3)
                                    <img src="{{ str_starts_with($tienda->vitrina_3, 'http') ? $tienda->vitrina_3 : Storage::url($tienda->vitrina_3) }}" class="absolute inset-0 h-full w-full object-cover">
                                    
                                    <div class="absolute inset-0 bg-black/45 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-150">
                                        <button type="button" onclick="document.getElementById('file-vitrina-slot-3').click()"
                                                class="p-2 bg-white text-slate-900 rounded-full shadow hover:scale-110 transition flex items-center justify-center cursor-pointer"
                                                title="Cambiar Imagen">
                                            <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('cliente.personalizar.vitrina.update') }}" enctype="multipart/form-data" class="hidden">
                                        @csrf
                                        <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                        <input type="hidden" name="slot" value="3">
                                        <input type="file" id="file-vitrina-slot-3" name="imagen" onchange="this.form.submit()">
                                    </form>
                                @else
                                    <div class="text-center p-2 flex flex-col items-center justify-center">
                                        <button onclick="document.getElementById('file-vitrina-slot-3-empty').click()" type="button" class="p-2 bg-indigo-600/90 hover:bg-indigo-700 text-white rounded-full shadow-lg transition hover:scale-110 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                        <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mt-1">Añadir Vitrina 3</span>
                                        <form method="POST" action="{{ route('cliente.personalizar.vitrina.update') }}" enctype="multipart/form-data" class="hidden">
                                            @csrf
                                            <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                            <input type="hidden" name="slot" value="3">
                                            <input type="file" id="file-vitrina-slot-3-empty" name="imagen" onchange="this.form.submit()">
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>   </div>
                </div>
            </div>
        @endif

        {{-- REPLICA DEL MODAL DE LA TIENDA --}}
        <template x-teleport="body">
            <div x-show="activeStore" 
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-8"
                x-transition.opacity.duration.300ms x-cloak>
                
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-xl" @click="activeStore = false"></div>

                <div x-show="activeStore"
                    class="relative w-full max-w-5xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl rounded-[3rem] shadow-2xl border border-white/40 overflow-hidden flex flex-col max-h-[90vh]"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-12"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                    
                    {{-- Modal Header --}}
                    <div class="relative p-6 md:p-10 text-white bg-gradient-to-br from-slate-600 to-slate-800">
                        <button
                            type="button"
                            @click="activeStore = false"
                            class="absolute top-4 right-4 md:top-8 md:right-8 z-20 p-2 rounded-full bg-white/20 backdrop-blur hover:bg-white/40 transition cursor-pointer">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <div class="relative flex items-center gap-4 md:gap-8">
                            
                            {{-- Brand Logo (con lapicito overlay) --}}
                            <div class="relative group/modal-logo h-16 w-16 md:h-24 md:w-24 rounded-2xl bg-white/30 backdrop-blur border border-white/50 flex items-center justify-center text-xl md:text-4xl font-black uppercase overflow-hidden shrink-0">
                                @if($marcaLogoUrl)
                                    <img src="{{ $marcaLogoUrl }}" class="h-full w-full object-cover">
                                @else
                                    <span>{{ substr($marcaNombre, 0, 2) }}</span>
                                @endif
                                
                                @if($currentBrand)
                                    <button type="button" onclick="document.getElementById('input-brand-logo-modal').click()"
                                            class="absolute inset-0 bg-black/55 flex items-center justify-center opacity-0 group-hover/modal-logo:opacity-100 transition cursor-pointer"
                                            title="Cambiar Logo">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('cliente.personalizar.marca.logo.update') }}" enctype="multipart/form-data" class="hidden">
                                        @csrf
                                        <input type="hidden" name="marca_id" value="{{ $currentBrand->id }}">
                                        <input type="file" id="input-brand-logo-modal" name="logo" onchange="this.form.submit()">
                                    </form>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-white/70 text-[10px] md:text-xs font-black tracking-[0.3em] uppercase">Local {{ $tienda->numero }}</span>
                                </div>

                                {{-- Editable Store Name (Modal Header) --}}
                                <form method="POST" action="{{ route('cliente.personalizar.tienda.update') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                    <input type="hidden" name="telefono_referencia" value="{{ $tienda->telefono_referencia }}">
                                    <input type="hidden" name="descripcion" value="{{ $tienda->descripcion }}">
                                    <input type="hidden" name="marca_id" value="{{ $currentBrand ? $currentBrand->id : '' }}">

                                    <div class="relative group/mname flex items-center">
                                        <input type="text" name="nombre" value="{{ $tienda->nombre }}" 
                                               class="bg-transparent border-0 border-b border-dashed border-transparent hover:border-white/50 focus:border-white focus:ring-0 text-xl md:text-4xl font-black tracking-tighter w-full p-0 outline-none rounded-none"
                                               title="Presione Enter para guardar">
                                        <span class="absolute right-2 opacity-0 group-hover/mname:opacity-100 text-white/70 pointer-events-none transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </span>
                                    </div>
                                </form>

                                {{-- Editable Store Description (Modal Header) --}}
                                <form method="POST" action="{{ route('cliente.personalizar.tienda.update') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                    <input type="hidden" name="nombre" value="{{ $tienda->nombre }}">
                                    <input type="hidden" name="telefono_referencia" value="{{ $tienda->telefono_referencia }}">
                                    <input type="hidden" name="marca_id" value="{{ $currentBrand ? $currentBrand->id : '' }}">

                                    <div class="relative group/mdesc">
                                        <textarea name="descripcion" rows="2" 
                                                  class="bg-transparent text-white/90 text-xs md:text-sm font-medium italic border-0 border-b border-dashed border-transparent hover:border-white/50 focus:border-white focus:ring-0 p-0 w-full outline-none resize-none rounded-none mt-1"
                                                  placeholder="Escribe una descripción comercial y presiona Enter para guardar..."
                                                  onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.form.submit(); }">{{ $tienda->descripcion }}</textarea>
                                        <span class="absolute top-1 right-2 opacity-0 group-hover/mdesc:opacity-100 text-white/70 pointer-events-none transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Body Content --}}
                    <div class="flex-1 overflow-y-auto p-6 md:p-10 space-y-8 bg-slate-50/50">
                        
                        {{-- Metadata grid (Brand dropdown, Inquilino, phone input) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            {{-- Brand dropdown selector card --}}
                            <div class="rounded-2xl border border-slate-200 p-4 bg-white shadow-sm space-y-2">
                                <div class="text-[9px] font-bold tracking-[0.2em] uppercase text-slate-400">Marca Asociada</div>
                                <form method="POST" action="{{ route('cliente.personalizar.tienda.update') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                    <input type="hidden" name="nombre" value="{{ $tienda->nombre }}">
                                    <input type="hidden" name="descripcion" value="{{ $tienda->descripcion }}">
                                    <input type="hidden" name="telefono_referencia" value="{{ $tienda->telefono_referencia }}">

                                    <select name="marca_id" onchange="this.form.submit()" 
                                            class="w-full rounded-xl border-slate-200 text-xs py-1.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 font-extrabold text-slate-800 bg-slate-50 cursor-pointer">
                                        <option value="">Monograma (Sin Marca)</option>
                                        @foreach($marcas as $m)
                                            <option value="{{ $m->id }}" {{ $currentBrand && $currentBrand->id === $m->id ? 'selected' : '' }}>
                                                {{ $m->nombre }} ({{ $m->cliente_id === null ? 'Pública' : 'Privada' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <div class="rounded-2xl border border-slate-200 p-4 bg-white shadow-sm space-y-1">
                                <div class="text-[9px] font-bold tracking-[0.2em] uppercase text-slate-400">Inquilino</div>
                                <div class="text-sm font-black text-slate-800 py-1">{{ $cliente->user->nombres }} {{ $cliente->user->apellido_paterno }}</div>
                            </div>

                            {{-- Editable Telephone --}}
                            <div class="rounded-2xl border border-slate-200 p-4 bg-white shadow-sm space-y-1">
                                <div class="text-[9px] font-bold tracking-[0.2em] uppercase text-slate-400">Teléfono Comercial</div>
                                <form method="POST" action="{{ route('cliente.personalizar.tienda.update') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                    <input type="hidden" name="nombre" value="{{ $tienda->nombre }}">
                                    <input type="hidden" name="descripcion" value="{{ $tienda->descripcion }}">
                                    <input type="hidden" name="marca_id" value="{{ $currentBrand ? $currentBrand->id : '' }}">

                                    <div class="relative group/mphone flex items-center">
                                        <input type="text" name="telefono_referencia" value="{{ $tienda->telefono_referencia }}" 
                                               class="bg-transparent border-0 border-b border-dashed border-transparent hover:border-slate-300 focus:border-indigo-500 focus:ring-0 text-sm font-black text-slate-800 p-0 w-full outline-none rounded-none py-1"
                                               placeholder="Sin número telefónico"
                                               title="Presione Enter para guardar">
                                        <span class="absolute right-1 opacity-0 group-hover/mphone:opacity-100 text-slate-400 pointer-events-none transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Catalog Section --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 md:h-10 md:w-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg">
                                    <svg class="w-4 h-4 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <h3 class="text-xl font-black text-slate-800">Catálogo de Productos</h3>
                            </div>

                            @if($tienda->productos->isEmpty())
                                <div class="rounded-3xl border border-dashed border-slate-300 p-8 text-center text-slate-500 italic">
                                    Esta tienda aún no cargó productos a su catálogo.
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($tienda->productos as $p)
                                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between group">
                                        <div>
                                            {{-- Product Image (con overlay de lapicito) --}}
                                            <div class="relative aspect-square rounded-2xl overflow-hidden bg-slate-100 mb-4 flex items-center justify-center group/pimg border border-slate-100 shadow-inner">
                                                <img src="{{ $p->imagenes->first() ? (str_starts_with($p->imagenes->first()->url, 'http') ? $p->imagenes->first()->url : Storage::url($p->imagenes->first()->url)) : '/images/placeholder.jpg' }}" class="h-full w-full object-cover">
                                                
                                                <div class="absolute inset-0 bg-black/45 flex items-center justify-center opacity-0 group-hover/pimg:opacity-100 transition duration-150">
                                                    <button type="button" onclick="document.getElementById('file-modal-prod-{{ $p->id }}').click()"
                                                            class="p-2.5 bg-white text-slate-900 rounded-full shadow-lg hover:scale-110 transition flex items-center justify-center cursor-pointer"
                                                            title="Cambiar Foto">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('cliente.personalizar.producto.update') }}" enctype="multipart/form-data" class="hidden">
                                                    @csrf
                                                    <input type="hidden" name="producto_id" value="{{ $p->id }}">
                                                    <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                                    <input type="hidden" name="nombre" value="{{ $p->nombre }}">
                                                    <input type="hidden" name="precio" value="{{ $p->precio }}">
                                                    <input type="file" id="file-modal-prod-{{ $p->id }}" name="imagen" onchange="this.form.submit()">
                                                </form>
                                            </div>

                                            {{-- Inline Product details form --}}
                                            <form method="POST" action="{{ route('cliente.personalizar.producto.update') }}" class="m-0 space-y-1.5">
                                                @csrf
                                                <input type="hidden" name="producto_id" value="{{ $p->id }}">
                                                <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                                                <input type="hidden" name="descripcion" value="{{ $p->descripcion }}">

                                                {{-- Product name input --}}
                                                <div class="relative group/pname flex items-center">
                                                    <input type="text" name="nombre" value="{{ $p->nombre }}" required 
                                                           class="font-black text-slate-800 text-sm truncate bg-transparent border-0 border-b border-dashed border-transparent hover:border-slate-300 focus:border-indigo-500 focus:ring-0 w-full p-0 outline-none rounded-none"
                                                           title="Presione Enter para guardar">
                                                    <span class="absolute right-1 opacity-0 group-hover/pname:opacity-100 text-slate-400 pointer-events-none transition">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                    </span>
                                                </div>

                                                {{-- Price input --}}
                                                <div class="flex items-center justify-between">
                                                    <div class="relative group/pprice flex items-center">
                                                        <span class="text-xs font-black text-emerald-600">Bs. </span>
                                                        <input type="number" step="0.01" name="precio" value="{{ $p->precio }}" required
                                                               class="text-sm font-black text-emerald-600 bg-transparent border-0 border-b border-dashed border-transparent hover:border-slate-300 focus:border-indigo-500 focus:ring-0 w-24 p-0 outline-none rounded-none"
                                                               title="Presione Enter para guardar">
                                                        <span class="ml-1 opacity-0 group-hover/pprice:opacity-100 text-slate-400 pointer-events-none transition">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">
                                                {{ $p->categoria?->nombre ?? 'General' }}
                                            </span>
                                            
                                            {{-- Delete product --}}
                                            <button type="button"
                                                    @click="openDeleteModal('{{ addslashes($p->nombre) }}', '{{ route('cliente.productos.destroy', $p->id) }}')"
                                                    class="text-xs font-bold text-rose-500 hover:text-rose-700 transition cursor-pointer">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Add Product Card --}}
                                <button type="button" @click="openCreateModal(null)"
                                        class="rounded-3xl border-2 border-dashed border-slate-300 hover:border-indigo-500 bg-white/40 hover:bg-white transition p-6 flex flex-col items-center justify-center text-center group gap-3 min-h-[250px] cursor-pointer">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 group-hover:bg-indigo-600 transition flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-sm text-slate-800">Agregar Producto</h4>
                                        <p class="text-xs text-slate-400 mt-1">Crea un nuevo producto en tu catálogo comercial</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL: CREAR PRODUCTO NUEVO --}}
        <div x-show="productModalOpen" class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-6" x-cloak>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="productModalOpen = false"></div>
            
            <div class="relative w-full max-w-xl bg-white rounded-[2.5rem] shadow-2xl border border-slate-200/80 overflow-hidden flex flex-col max-h-[90vh]"
                 x-show="productModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-6"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                
                <div class="p-6 md:p-8 bg-indigo-700 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-lg font-black">Registrar Nuevo Producto</h3>
                        <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest mt-1">Se añadirá automáticamente al catálogo de la tienda</p>
                    </div>
                    <button type="button" @click="productModalOpen = false" class="p-2 text-white/70 hover:text-white rounded-full bg-white/10 hover:bg-white/20 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('cliente.personalizar.producto.update') }}" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 md:p-8 space-y-5">
                    @csrf
                    <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">
                    <input type="hidden" name="producto_id" value="">

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nombre del Producto</label>
                        <input type="text" name="nombre" required 
                               placeholder="Ej: Tenis Air Max" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Precio (Bs.)</label>
                        <input type="number" step="0.01" name="precio" required 
                               placeholder="Ej: 350.00" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    {{-- Categorías & Subcategorías --}}
                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Categoría Principal</label>
                            <select name="categoria_id" x-model="selectedCategory" required class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Seleccione categoría principal</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.nombre"></option>
                                </template>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Subcategoría Específica</label>
                            <select name="subcategoria_id" x-model="selectedSubcategory" required class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Seleccione subcategoría</option>
                                <template x-for="sub in subcategories" :key="sub.id">
                                    <option :value="sub.id" x-text="sub.nombre"></option>
                                </template>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Marca</label>
                             <select name="marca_id" required class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Seleccione una marca</option>
                                @foreach($marcas as $m)
                                    <option value="{{ $m->id }}">{{ $m->nombre }} ({{ $m->cliente_id === null ? 'Pública' : 'Privada' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Descripción / Detalles del Producto</label>
                        <textarea name="descripcion" rows="3" 
                                  placeholder="Características, colores, tamaños disponibles..." 
                                  class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                    </div>

                    {{-- Imagen --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Imagen del Producto (Requerido)</label>
                        <input type="file" name="imagen" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-[10px] text-slate-400 font-medium">Recomendado: Imágenes cuadradas, JPG o PNG de hasta 5MB.</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="productModalOpen = false" class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg transition cursor-pointer">
                            Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.client>
