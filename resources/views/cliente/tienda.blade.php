<x-layouts.client title="Configuración de mi Tienda">

    <div class="max-w-4xl mx-auto space-y-8">
        
        @if($tiendas->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center text-slate-500">
                Aún no tienes locales asignados por el administrador.
            </div>
        @else
            @foreach($tiendas as $tienda)
                <div class="bg-white rounded-[2.5rem] border border-slate-200/60 p-6 md:p-10 space-y-8 shadow-sm">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">
                            Local N° {{ $tienda->numero }} — {{ $tienda->piso->infraestructura->nombre }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase font-bold tracking-widest">
                            {{ $tienda->piso->nombre }} • Superficie física asignada
                        </p>
                    </div>

                    <form method="POST" action="{{ route('cliente.tienda.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="tienda_id" value="{{ $tienda->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre comercial -->
                            <div class="space-y-2">
                                <label for="nombre-{{ $tienda->id }}" class="block text-sm font-bold text-slate-700">Nombre de la Tienda</label>
                                <input type="text" name="nombre" id="nombre-{{ $tienda->id }}" value="{{ old('nombre', $tienda->nombre) }}" 
                                       placeholder="Ej: Velvet Premium" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Teléfono de referencia -->
                            <div class="space-y-2">
                                <label for="tel-{{ $tienda->id }}" class="block text-sm font-bold text-slate-700">Teléfono de Atención / Whatsapp</label>
                                <input type="text" name="telefono_referencia" id="tel-{{ $tienda->id }}" value="{{ old('telefono_referencia', $tienda->telefono_referencia) }}" 
                                       placeholder="Ej: +591 72200000" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Descripción de la tienda -->
                            <div class="space-y-2 md:col-span-2">
                                <label for="desc-{{ $tienda->id }}" class="block text-sm font-bold text-slate-700">Descripción del Local</label>
                                <textarea name="descripcion" id="desc-{{ $tienda->id }}" rows="4" 
                                          placeholder="Describe tu tienda, promociones o marca al público..." 
                                          class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('descripcion', $tienda->descripcion) }}</textarea>
                            </div>


                        </div>

                        <div class="pt-4">
                            <button type="submit" class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/20 transition">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        @endif

    </div>

</x-layouts.client>
