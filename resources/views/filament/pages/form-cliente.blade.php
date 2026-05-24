<div class="fi-resource-create-record-page fi-resource-clientes pt-10 pb-12">
    <form wire:submit="{{ $this instanceof \Filament\Resources\Pages\CreateRecord ? 'create' : 'save' }}" class="space-y-6">
        {{-- Header del Formulario --}}
        <div class="flex items-center justify-between mb-6 px-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $this instanceof \Filament\Resources\Pages\CreateRecord ? 'Crear cliente' : 'Editar cliente' }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $this instanceof \Filament\Resources\Pages\CreateRecord ? 'Completa la información para registrar un nuevo cliente.' : 'Actualiza los datos del cliente y su cuenta.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ $this->getResource()::getUrl('index') }}" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg bg-red-600 text-white shadow-sm hover:bg-red-500 px-4 py-2 text-sm inline-grid">
                    Cancelar
                </a>
                <button type="submit" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-btn-color-primary bg-primary-600 text-white shadow-sm hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 px-4 py-2 text-sm inline-grid">
                    {{ $this instanceof \Filament\Resources\Pages\CreateRecord ? 'Guardar cliente' : 'Actualizar cliente' }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start px-4">
            
            {{-- COLUMNA IZQUIERDA: INFORMACIÓN REQUERIDA (OSCURA) --}}
            <div class="lg:col-span-5 bg-slate-800 rounded-3xl p-8 text-white shadow-2xl ring-1 ring-white/10 min-h-full">
                <style>
                    /* Forzar texto blanco en toda la sección oscura */
                    .custom-form-dark-container *,
                    .custom-form-dark-container, 
                    .custom-form-dark-container label, 
                    .custom-form-dark-container .fi-fo-field-wrp-label label {
                        color: #ffffff !important;
                    }

                    /* Asteriscos de campos requeridos en rojo brillante */
                    .custom-form-dark-container .fi-fo-field-wrp-label span {
                        color: #ff4d4d !important;
                    }
                    /* Inputs con fondo gris semitransparente pero texto blanco */ 
                    .custom-form-dark-container input, 
                    .custom-form-dark-container select,
                    .custom-form-dark-container .fi-input-wrp {
                        background-color: rgba(255, 255, 255, 0.08) !important; 
                        border-color: rgba(255, 255, 255, 0.15) !important;
                        color: #ffffff !important;
                        border-radius: 0.75rem !important;
                        box-shadow: none !important;
                    }
                    /* CASO ESPECÍFICO SELECT: Asegurar que el texto sea visible */
                    .custom-form-dark-container select option {
                        background-color: #1e293b !important;
                        color: #ffffff !important;
                    }
                    /* Selector de código de país: si es un dropdown blanco, forzar texto negro */
                    .fi-select-input-list-box {
                        color: #000000 !important;
                    }

                    /* --- ESTILOS DROPDOWN CÓDIGO DE PAÍS (OSCURO) --- */
                    .country-code-container .fi-dropdown-panel {
                        background-color: #18293F !important;
                        border: 1px solid rgba(255, 255, 255, 0.1) !important;
                    }
                    .country-code-container .fi-select-input-option,
                    .country-code-container .fi-dropdown-list-item {
                        background-color: #18293F !important;
                        color: #ffffff !important;
                    }
                    /* Item seleccionado o con hover */
                    .country-code-container .fi-select-input-option:hover,
                    .country-code-container .fi-select-input-option:focus,
                    .country-code-container .fi-select-input-option[data-active-item],
                    .country-code-container .fi-dropdown-list-item:hover,
                    .country-code-container .fi-dropdown-list-item[data-active-item] {
                        background-color: #2C3A4E !important;
                    }

                    /* --- ESTILOS DROPDOWN GÉNERO (CLARO) --- */
                    .gender-container .fi-dropdown-panel {
                        background-color: #FFFFFF !important;
                        border: 1px solid rgba(0, 0, 0, 0.1) !important;
                    }
                    .gender-container .fi-select-input-option,
                    .gender-container .fi-dropdown-list-item {
                        background-color: #FFFFFF !important;
                        color: #111827 !important;
                    }
                    /* Item seleccionado o con hover */
                    .gender-container .fi-select-input-option:hover,
                    .gender-container .fi-select-input-option:focus,
                    .gender-container .fi-select-input-option[data-active-item],
                    .gender-container .fi-dropdown-list-item:hover,
                    .gender-container .fi-dropdown-list-item[data-active-item] {
                        background-color: #F2F2F2 !important;
                    }

                    /* Helper text y placeholders un poco más claros para contraste */
                    .custom-form-dark-container .fi-fo-field-wrp-helper-text,
                    .custom-form-dark-container ::placeholder {
                        color: #94a3b8 !important;
                    }

                    /* Quitar flechas de inputs numéricos */
                    .custom-form-dark-container input::-webkit-outer-spin-button,
                    .custom-form-dark-container input::-webkit-inner-spin-button {
                        -webkit-appearance: none;
                        margin: 0;
                    }
                    .custom-form-dark-container input[type=number] {
                        -moz-appearance: textfield;
                    }
                </style>
                <div class="flex items-center gap-3 mb-8">
                    <div class="p-2 bg-indigo-500/20 rounded-lg text-indigo-400 border border-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Información requerida</h2>
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5">Campos obligatorios</p>
                    </div>
                </div>

                <div class="space-y-8">
                    {{-- Campos requeridos --}}
                    <div class="space-y-6 custom-form-dark-container">
                        {{ $this->form->getComponent('nombres') }}
                        {{ $this->form->getComponent('apellido_paterno') }}
                        {{ $this->form->getComponent('ci') }}
                        {{ $this->form->getComponent('email') }}
                        
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-1 country-code-container">
                                {{ $this->form->getComponent('codigo_pais') }}
                            </div>
                            <div class="col-span-2">
                                {{ $this->form->getComponent('numero_celular') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: INFORMACIÓN OPCIONAL (CLARA) --}}
            <div class="lg:col-span-7 space-y-6">
                
                {{-- CARD: FOTO DEL USUARIO --}}
                <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-sm ring-1 ring-gray-950/5 border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-amber-600 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Imagen de perfil</h2>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <div class="flex-shrink-0 relative">
                            {{-- Loading indicator for upload --}}
                            <div wire:loading wire:target="foto_upload" class="absolute inset-0 bg-white/60 dark:bg-gray-800/60 flex items-center justify-center rounded-full z-10">
                                <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            @if ($foto_upload && method_exists($foto_upload, 'temporaryUrl'))
                                <img src="{{ $foto_upload->temporaryUrl() }}" class="w-24 h-24 rounded-full object-cover ring-4 ring-gray-50 shadow-sm">
                            @elseif($this instanceof \Filament\Resources\Pages\EditRecord && $record->foto)
                                <img src="{{ asset('storage/' . $record->foto) }}" class="w-24 h-24 rounded-full object-cover ring-4 ring-gray-50 shadow-sm">
                            @else
                                <div class="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Subir nueva foto</label>
                            <input type="file" wire:model="foto_upload" accept="image/png, image/jpeg, image/jpg, image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition cursor-pointer">
                            @error('foto_upload') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            <p class="mt-2 text-xs text-gray-400">JPG, PNG o WebP. Máx. 15MB</p>
                        </div>
                    </div>
                </div>

                {{-- CARD: INFORMACIÓN OPCIONAL --}}
                <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-sm ring-1 ring-gray-950/5 border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-8 border-b border-gray-50 dark:border-gray-800 pb-4">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Información opcional</h2>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mt-0.5">Campos adicionales</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{ $this->form->getComponent('apellido_materno') }}
                        <div class="gender-container">
                            {{ $this->form->getComponent('genero') }}
                        </div>
                        <div class="md:col-span-2">
                            {{ $this->form->getComponent('correo_secundario') }}
                        </div>
                    </div>
                </div>

                {{-- CARD: DOCUMENTOS ADJUNTOS (Lógica Nativa Livewire) --}}
                <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-sm ring-1 ring-gray-950/5 border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-8 border-b border-gray-50 dark:border-gray-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-purple-600 dark:text-purple-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-2.828-6.828l-6.414 6.586a6 6 0 008.485 8.485L21 11"/></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Documentos adjuntos</h2>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mt-0.5">Soportes digitales</p>
                            </div>
                        </div>
                        <button type="button" wire:click="addDocumento" class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-xs font-bold rounded-lg text-indigo-600 hover:bg-indigo-50 transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar Documento
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach($documentos_upload as $index => $doc)
                            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 relative group">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Selector Tipo --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo de Documento</label>
                                        <select wire:model="documentos_upload.{{ $index }}.tipo" class="block w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="contrato">Contrato</option>
                                            <option value="ci">Carnet de Identidad</option>
                                        </select>
                                    </div>
                                    {{-- Input Archivo --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Archivo</label>
                                        @if(isset($doc['existente']) && $doc['existente'])
                                            <div class="flex items-center gap-2 text-xs text-indigo-600 font-medium bg-white p-2 rounded-lg border">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                {{ basename($doc['existente']) }}
                                            </div>
                                        @else
                                            <input type="file" wire:model="documentos_upload.{{ $index }}.archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,image/*" class="block w-full text-xs text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 cursor-pointer">
                                            @error("documentos_upload.{$index}.archivo") <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                                            <p class="text-[9px] text-gray-400 mt-1">PDF, Word, Excel o Imagen. Máx. 100MB</p>
                                        @endif
                                    </div>
                                </div>
                                {{-- Botón Eliminar --}}
                                <button type="button" wire:click="removeDocumento({{ $index }})" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 shadow-sm hover:bg-red-200 transition opacity-0 group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach

                        @if(count($documentos_upload) === 0)
                            <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-2xl">
                                <p class="text-sm text-gray-400 italic">No hay documentos seleccionados</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer info --}}
        <div class="mx-4 mt-8 bg-gray-100/50 dark:bg-gray-800/50 p-4 rounded-xl flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Los campos marcados con <span class="text-red-500 font-bold">*</span> son obligatorios para completar el registro.
        </div>
    </form>
</div>
