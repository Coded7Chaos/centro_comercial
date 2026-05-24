<div class="bg-gray-50/50 min-h-screen p-4 sm:p-8">
    {{-- Contenedor Principal --}}
    <div class="max-w-7xl mx-auto space-y-6">
        
        {{-- CABECERA PREMIUM --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-950/5 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="relative">
                    @if($record->foto)
                        <img src="{{ asset('storage/' . $record->foto) }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover ring-4 ring-gray-50">
                    @else
                        <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center ring-4 ring-gray-50">
                            <span class="text-3xl font-bold text-indigo-600">{{ substr($record->user->nombres, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ $record->user->nombres }} {{ $record->user->apellido_paterno }} {{ $record->user->apellido_materno }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400 font-medium">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Registrado: {{ $record->created_at->format('d/m/Y H:i') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Actualizado: {{ $record->updated_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ \App\Filament\Resources\Clientes\ClientesResource::getUrl('index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al listado
                </a>
                <a href="{{ \App\Filament\Resources\Clientes\ClientesResource::getUrl('edit', ['record' => $record]) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Editar
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- COLUMNA IZQUIERDA: INFORMACIÓN PERSONAL --}}
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-950/5 p-8">
                <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-50">
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wider">Información personal</h2>
                </div>

                <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
                    {{-- Nombres --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Nombres
                        </label>
                        <p class="text-sm font-semibold text-gray-700">{{ $record->user->nombres }}</p>
                    </div>

                    {{-- Apellidos --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Apellidos
                        </label>
                        <p class="text-sm font-semibold text-gray-700">{{ $record->user->apellido_paterno }} {{ $record->user->apellido_materno }}</p>
                    </div>

                    {{-- CI --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            CI (Carnet de identidad)
                        </label>
                        <p class="text-sm font-semibold text-gray-700">{{ $record->ci }}</p>
                    </div>

                    {{-- Género --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                            Género
                        </label>
                        <p class="text-sm font-semibold text-gray-700">{{ ucfirst($record->genero ?? 'No definido') }}</p>
                    </div>

                    {{-- Email --}}
                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Correo electrónico
                        </label>
                        <p class="text-sm font-semibold text-indigo-600 break-all">{{ $record->user->email }}</p>
                    </div>

                    {{-- Teléfono --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 011.94.31l-.74 2.28a1 1 0 01-1.17.69l-3.14-.35a10 10 0 008.42 8.42l.35-3.14a1 1 0 01.69-1.17l2.28-.74a1 1 0 011.06.54l1.1 2.2a1 1 0 01-.1 1.22l-1.4 1.4a9 9 0 01-11 0l-1.4-1.4a1 1 0 01-.1-1.22l1.1-2.2z"/></svg>
                            Número de contacto
                        </label>
                        <p class="text-sm font-semibold text-gray-700">{{ $record->codigo_pais }} {{ $record->numero_celular }}</p>
                    </div>

                    {{-- Correo secundario (Condicional) --}}
                    @if($record->correo_secundario)
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Correo secundario
                        </label>
                        <p class="text-sm font-semibold text-gray-700">{{ $record->correo_secundario }}</p>
                    </div>
                    @endif

                    {{-- Tiendas Asociadas --}}
                    <div class="space-y-2 sm:col-span-2 mt-4 pt-4 border-t border-gray-50">
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Tiendas asociadas
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @forelse($record->tiendas as $tienda)
                                <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100">
                                    {{ $tienda->nombre ?: "Tienda #{$tienda->numero}" }}
                                </span>
                            @empty
                                <p class="text-sm italic text-gray-400">Sin locales asociados</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: ARCHIVOS ADJUNTOS --}}
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-950/5 p-8">
                <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-50">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-2.828-6.828l-6.414 6.586a6 6 0 008.485 8.485L21 11"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wider">Archivos adjuntos</h2>
                </div>

                <div class="space-y-4">
                    @forelse($record->documentos as $doc)
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 group transition hover:bg-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="p-2 bg-white rounded-xl shadow-sm">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-700 capitalize">{{ $doc->tipo == 'ci' ? 'Carnet de Identidad' : 'Contrato Firmado' }}</h3>
                                        <p class="text-xs text-gray-400 uppercase tracking-tighter">{{ basename($doc->url) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ asset('storage/' . $doc->url) }}" target="_blank" class="p-2 bg-white rounded-lg shadow-sm text-gray-400 hover:text-indigo-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ asset('storage/' . $doc->url) }}" download class="p-2 bg-white rounded-lg shadow-sm text-gray-400 hover:text-indigo-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="mx-auto w-12 h-12 text-gray-200 mb-4">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm text-gray-400 italic">No hay documentos cargados</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
