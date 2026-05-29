<x-layouts.client title="Editar Producto">

    <div class="max-w-3xl mx-auto">
        
        <!-- BACK ACTIONS -->
        <div class="mb-6">
            <a href="{{ route('cliente.productos.index') }}" class="text-slate-500 hover:text-slate-800 font-bold text-sm inline-flex items-center gap-1">
                &larr; Volver al Catálogo
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-slate-200/60 p-6 md:p-10 space-y-8 shadow-sm">
            <div>
                <h3 class="text-xl font-black text-slate-800">Modificar Producto</h3>
                <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">Edita la información de tu producto de catálogo</p>
            </div>

            <form method="POST" action="{{ route('cliente.productos.update', $producto->id) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div class="space-y-2">
                        <label for="nombre" class="block text-sm font-bold text-slate-700">Nombre del Producto</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}" required
                               placeholder="Ej: Tenis Deportivos Runner" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <!-- Precio -->
                    <div class="space-y-2">
                        <label for="precio" class="block text-sm font-bold text-slate-700">Precio (Bs.)</label>
                        <input type="number" step="0.01" name="precio" id="precio" value="{{ old('precio', $producto->precio) }}" required
                               placeholder="0.00" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <!-- Tienda / Local -->
                    <div class="space-y-2">
                        <label for="infraestructuras_tienda_id" class="block text-sm font-bold text-slate-700">Local de venta</label>
                        <select name="infraestructuras_tienda_id" id="infraestructuras_tienda_id" required class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach($tiendas as $t)
                                <option value="{{ $t->id }}" {{ old('infraestructuras_tienda_id', $producto->infraestructuras_tienda_id) == $t->id ? 'selected' : '' }}>
                                    {{ $t->nombre ?: 'Local '.$t->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Marca -->
                    <div class="space-y-2">
                        <label for="marca_id" class="block text-sm font-bold text-slate-700">Marca</label>
                        <select name="marca_id" id="marca_id" required class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach($marcas as $m)
                                <option value="{{ $m->id }}" {{ old('marca_id', $producto->marca_id) == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categoría Padre -->
                    <div class="space-y-2">
                        <label for="categoria_id" class="block text-sm font-bold text-slate-700">Categoría</label>
                        <select name="categoria_id" id="categoria_id" required onchange="filterSubcategories()" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">Seleccionar categoría...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ $categoriaPadreId == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subcategoría -->
                    <div class="space-y-2">
                        <label for="subcategoria_id" class="block text-sm font-bold text-slate-700">Subcategoría</label>
                        <select name="subcategoria_id" id="subcategoria_id" required class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">Seleccionar subcategoría...</option>
                            @foreach($categorias as $cat)
                                @foreach($cat->subcategorias as $sub)
                                    <option value="{{ $sub->id }}" data-parent="{{ $cat->id }}" 
                                            class="{{ $categoriaPadreId == $cat->id ? '' : 'hidden' }}"
                                            {{ old('subcategoria_id', $producto->categoria_id) == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->nombre }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2 md:col-span-2">
                        <label for="descripcion" class="block text-sm font-bold text-slate-700">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="4" placeholder="Detalla las especificaciones de tu producto..." 
                                  class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    </div>

                    <!-- Imagen -->
                    <div class="space-y-4 md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700">Fotografía del Producto</label>
                        
                        <div class="flex flex-col md:flex-row items-center gap-6">
                            <div class="w-24 h-24 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                @if($producto->imagenes->first())
                                    @php $url = $producto->imagenes->first()->url; @endphp
                                    <img src="{{ str_starts_with($url, 'http') ? $url : Storage::url($url) }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @endif
                            </div>
                            <div class="flex-1 w-full space-y-2">
                                <input type="file" name="imagen" id="imagen" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <p class="text-[10px] text-slate-400 font-medium">Sube una nueva foto solo si deseas reemplazar la actual.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/20 transition">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- SCRIPT FILTRO DINÁMICO DE SUBCATEGORÍAS -->
    <script>
        function filterSubcategories() {
            const parentId = document.getElementById('categoria_id').value;
            const subSelect = document.getElementById('subcategoria_id');
            const options = subSelect.querySelectorAll('option');
            
            subSelect.value = '';
            options.forEach(opt => {
                if (opt.value === '') {
                    opt.classList.remove('hidden');
                    return;
                }
                if (opt.getAttribute('data-parent') === parentId) {
                    opt.classList.remove('hidden');
                    opt.disabled = false;
                } else {
                    opt.classList.add('hidden');
                    opt.disabled = true;
                }
            });
        }
    </script>

</x-layouts.client>
