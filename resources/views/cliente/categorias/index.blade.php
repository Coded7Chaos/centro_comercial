<x-layouts.client title="Categorías">
<div class="space-y-8"
     x-data="{
         subcatDe: null,
         modal: false,
         deleteName: '',
         deleteUrl: '',
         openModal(name, url) {
             this.deleteName = name;
             this.deleteUrl  = url;
             this.modal      = true;
         }
     }">

    {{-- ── TOAST (éxito) ─────────────────────────────────────────────────── --}}
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
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
            <button @click="visible = false" class="ml-2 opacity-70 hover:opacity-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- ── TOAST (error) ─────────────────────────────────────────────────── --}}
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
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
            <button @click="visible = false" class="ml-2 opacity-70 hover:opacity-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- ── MODAL DE CONFIRMACIÓN ──────────────────────────────────────────── --}}
    <div x-show="modal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="modal = false">

        {{-- Fondo oscuro --}}
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modal = false"></div>

        {{-- Panel del modal --}}
        <div x-show="modal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 space-y-5">

            {{-- Icono --}}
            <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-2xl mx-auto">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>

            {{-- Texto --}}
            <div class="text-center space-y-1">
                <h3 class="text-lg font-black text-slate-800">¿Eliminar categoría?</h3>
                <p class="text-sm text-slate-500">
                    Estás a punto de eliminar
                    <strong class="text-slate-700" x-text="'«' + deleteName + '»'"></strong>.
                    Esta acción no se puede deshacer.
                </p>
            </div>

            {{-- Botones --}}
            <div class="flex gap-3 pt-1">
                <button type="button"
                        @click="modal = false"
                        class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                    Cancelar
                </button>

                {{-- Formulario que se envía al confirmar --}}
                <form :action="deleteUrl" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition shadow-sm shadow-red-600/30">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── HEADER ─────────────────────────────────────────────────────────── --}}
    <div>
        <h3 class="text-xl font-black text-slate-800">Categorías</h3>
        <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">Organiza tus productos con categorías y subcategorías</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

        {{-- ── COLUMNA IZQUIERDA: LISTA ────────────────────────────────── --}}
        <div class="lg:col-span-3 space-y-4">

            @forelse($categorias as $cat)
                <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">

                    {{-- Cabecera de categoría --}}
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-black text-slate-800 text-sm">{{ $cat->nombre }}</p>
                                @if($cat->descripcion)
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $cat->descripcion }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- Botón agregar subcategoría --}}
                            <button @click="subcatDe = subcatDe === {{ $cat->id }} ? null : {{ $cat->id }}"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 border border-indigo-200 hover:bg-indigo-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Subcategoría
                            </button>
                            {{-- Botón eliminar categoría → abre modal --}}
                            <button type="button"
                                    @click="openModal('{{ addslashes($cat->nombre) }}', '{{ route('cliente.categorias.destroy', $cat->id) }}')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition"
                                    title="Eliminar categoría">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Mini-form inline para nueva subcategoría --}}
                    <div x-show="subcatDe === {{ $cat->id }}"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-indigo-50/60 border-b border-indigo-100 px-5 py-4">
                        <form method="POST" action="{{ route('cliente.categorias.store') }}" class="flex items-center gap-3">
                            @csrf
                            <input type="hidden" name="categoria_padre_id" value="{{ $cat->id }}">
                            <input type="text" name="nombre" required placeholder="Nombre de la subcategoría"
                                   class="flex-1 px-3 py-2 rounded-xl border border-indigo-200 bg-white text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                                Agregar
                            </button>
                            <button type="button" @click="subcatDe = null"
                                    class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                                Cancelar
                            </button>
                        </form>
                    </div>

                    {{-- Subcategorías como chips --}}
                    @if($cat->subcategorias->isNotEmpty())
                        <div class="px-5 py-3 flex flex-wrap gap-2">
                            @foreach($cat->subcategorias as $sub)
                                <div class="flex items-center gap-1.5 bg-slate-100 rounded-full pl-3 pr-1.5 py-1">
                                    <span class="text-xs font-semibold text-slate-700">{{ $sub->nombre }}</span>
                                    <button type="button"
                                            @click="openModal('{{ addslashes($sub->nombre) }}', '{{ route('cliente.categorias.destroy', $sub->id) }}')"
                                            class="w-4 h-4 flex items-center justify-center rounded-full text-slate-400 hover:bg-red-100 hover:text-red-500 transition"
                                            title="Eliminar subcategoría">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="px-5 py-3 text-xs text-slate-400 italic">Sin subcategorías aún.</p>
                    @endif

                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-12 text-center text-slate-400">
                    <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <p class="text-sm font-semibold">Aún no hay categorías creadas.</p>
                    <p class="text-xs mt-1">Usa el formulario de la derecha para crear la primera.</p>
                </div>
            @endforelse
        </div>

        {{-- ── COLUMNA DERECHA: FORMULARIO ────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-6 sticky top-6 space-y-5">
                <div>
                    <h4 class="font-black text-slate-800">Nueva categoría</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Para crear una subcategoría, elige una categoría padre.</p>
                </div>

                <form method="POST" action="{{ route('cliente.categorias.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-1.5">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" required value="{{ old('nombre') }}"
                               placeholder="Ej. Electrónica, Ropa Mujer..."
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('nombre') border-red-400 @enderror">
                        @error('nombre')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-1.5">
                            Descripción <span class="text-slate-300 font-normal">(opcional)</span>
                        </label>
                        <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                               placeholder="Breve descripción..."
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-1.5">
                            Categoría padre
                            <span class="text-slate-300 font-normal">(deja vacío para categoría principal)</span>
                        </label>
                        <select name="categoria_padre_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
                            <option value="">— Categoría principal —</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('categoria_padre_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                        Crear categoría
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
</x-layouts.client>
