@php
    $isEdit = isset($marca);
    $action = $isEdit ? route('cliente.marcas.update', $marca->id) : route('cliente.marcas.store');
    $title = $isEdit ? 'Editar Marca Privada' : 'Registrar Marca Privada';
    $subtitle = $isEdit ? 'Modifica los detalles de tu marca privada' : 'Registra un nuevo nombre y logo para tu marca privada';
    $buttonText = $isEdit ? 'Guardar Cambios' : 'Registrar Marca';
@endphp

<x-layouts.client :title="$title">

    <div class="max-w-3xl mx-auto">
        
        <!-- BACK ACTIONS -->
        <div class="mb-6">
            <a href="{{ route('cliente.marcas.index') }}" class="text-slate-500 hover:text-slate-800 font-bold text-sm inline-flex items-center gap-1">
                &larr; Volver a Mis Marcas
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-slate-200/60 p-6 md:p-10 space-y-8 shadow-sm">
            <div>
                <h3 class="text-xl font-black text-slate-800">{{ $title }}</h3>
                <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">{{ $subtitle }}</p>
            </div>

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-bold space-y-1">
                    @foreach($errors->all() as $err)
                        <div>• {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    <!-- Nombre -->
                    <div class="space-y-2">
                        <label for="nombre" class="block text-sm font-bold text-slate-700">Nombre de la Marca</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $isEdit ? $marca->nombre : '') }}" required
                               placeholder="Ej: Perez Tech" class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <p class="text-[10px] text-slate-400 font-medium">Debe tener entre 3 y 60 caracteres y contener letras.</p>
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2">
                        <label for="descripcion" class="block text-sm font-bold text-slate-700">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="4" placeholder="Detalla los valores o características de tu marca comercial..." 
                                  class="w-full rounded-2xl border-slate-200 py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('descripcion', $isEdit ? $marca->descripcion : '') }}</textarea>
                        <p class="text-[10px] text-slate-400 font-medium">Opcional. Si se completa, debe contener al menos una letra (máximo 500 caracteres).</p>
                    </div>

                    <!-- Logo -->
                    <div class="space-y-4">
                        <label for="logo" class="block text-sm font-bold text-slate-700">Logo de la Marca</label>
                        
                        @if($isEdit && $marca->logo)
                            <div class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 bg-slate-50/50">
                                <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm">
                                    <img src="{{ Storage::url($marca->logo) }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-500">Logo Actual</span>
                                    <p class="text-[10px] text-slate-400">Si subes un archivo nuevo, reemplazará la imagen actual.</p>
                                </div>
                            </div>
                        @endif

                        <input type="file" name="logo" id="logo" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-[10px] text-slate-400 font-medium">Solo imágenes JPG, JPEG, PNG o WEBP. Máximo 2MB.</p>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/20 transition">
                        {{ $buttonText }}
                    </button>
                    
                    <a href="{{ route('cliente.marcas.index') }}" class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

    </div>

</x-layouts.client>
