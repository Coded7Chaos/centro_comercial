<x-layouts.client title="Gestión de Productos">

    <div class="space-y-8">
        
        <!-- HEADER ACTIONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-black text-slate-800">Catálogo de Productos</h3>
                <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">Productos expuestos al público en el mall virtual</p>
            </div>
            
            <a href="{{ route('cliente.productos.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/25 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Agregar Producto
            </a>
        </div>

        <!-- PRODUCT LIST TABLE -->
        <div class="bg-white rounded-[2rem] border border-slate-200/60 overflow-hidden shadow-sm">
            @if($productos->isEmpty())
                <div class="p-12 text-center text-slate-400 italic">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Aún no has registrado ningún producto en tu catálogo. ¡Comienza agregando uno!
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-500">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-700 uppercase tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Producto</th>
                                <th class="px-6 py-4">Tienda / Local</th>
                                <th class="px-6 py-4">Categoría</th>
                                <th class="px-6 py-4">Marca</th>
                                <th class="px-6 py-4">Precio</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($productos as $p)
                                @php
                                    $img = $p->imagenes->first();
                                    $imgUrl = $img ? $img->url : null;
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <!-- Producto -->
                                    <td class="px-6 py-4 flex items-center gap-4 text-slate-900">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                                            @if($imgUrl)
                                                <img src="{{ str_starts_with($imgUrl, 'http') ? $imgUrl : Storage::url($imgUrl) }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm">{{ $p->nombre }}</div>
                                            <div class="text-[10px] text-slate-400 truncate max-w-[150px]">{{ $p->descripcion ?: 'Sin descripción' }}</div>
                                        </div>
                                    </td>
                                    
                                    <!-- Tienda -->
                                    <td class="px-6 py-4 text-slate-800 text-xs">
                                        {{ $p->tienda->nombre ?: 'Local '.$p->tienda->numero }}
                                    </td>

                                    <!-- Categoría -->
                                    <td class="px-6 py-4 text-slate-500 text-xs">
                                        {{ $p->categoria->nombre ?? 'General' }}
                                    </td>

                                    <!-- Marca -->
                                    <td class="px-6 py-4 text-slate-500 text-xs">
                                        {{ $p->marca->nombre ?? 'N/A' }}
                                    </td>

                                    <!-- Precio -->
                                    <td class="px-6 py-4 text-slate-900 font-extrabold text-sm">
                                        Bs. {{ number_format($p->price ?? $p->precio, 2) }}
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('cliente.productos.edit', $p->id) }}" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            
                                            <form method="POST" action="{{ route('cliente.productos.destroy', $p->id) }}" onsubmit="return confirm('¿Está seguro de eliminar este producto del catálogo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</x-layouts.client>
