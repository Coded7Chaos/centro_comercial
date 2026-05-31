<div class="space-y-8 bg-slate-50/50 p-4 sm:p-6 rounded-3xl">

    {{-- HEADER --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Configuración de Tarifas y Descuentos</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Gestione etiquetas de tamaño, precios mensuales base y reglas de descuentos por tiempo.</p>
        </div>
    </div>

    {{-- TOAST / FLASH MESSAGES --}}
    @if (session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- TWO COLUMN GRID FOR SIZE LABELS AND SIZE PRICES --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- SECCIÓN A: ETIQUETAS DE TAMAÑO --}}
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-6">
            <div class="flex items-center gap-2 pb-4 border-b border-slate-100">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Etiquetas de Tamaño</h2>
            </div>

            {{-- Formulario de Etiquetas --}}
            <form wire:submit.prevent="saveEtiqueta" class="bg-slate-50 p-4 rounded-2xl border border-slate-150 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Etiqueta</label>
                        <input type="text" wire:model="etiquetaNombre" required placeholder="Ej. Pequeño"
                            class="block w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 bg-white font-semibold">
                        @error('etiquetaNombre') <p class="text-red-500 text-[10px]">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Desde (m²)</label>
                        <input type="number" step="0.01" wire:model="etiquetaDesde" required placeholder="0.00"
                            class="block w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 bg-white font-semibold">
                        @error('etiquetaDesde') <p class="text-red-500 text-[10px]">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Hasta (m²)</label>
                        <input type="number" step="0.01" wire:model="etiquetaHasta" required placeholder="9999.00"
                            class="block w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 bg-white font-semibold">
                        @error('etiquetaHasta') <p class="text-red-500 text-[10px]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    @if($etiquetaId)
                        <button type="button" wire:click="cancelEdit"
                            class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition rounded-lg font-bold text-xs uppercase tracking-wider">
                            Cancelar
                        </button>
                    @endif
                    <button type="submit"
                        class="px-4 py-1.5 bg-slate-900 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs uppercase tracking-wider shadow-sm transition">
                        {{ $etiquetaId ? 'Actualizar' : 'Agregar' }}
                    </button>
                </div>
            </form>

            {{-- Tabla de Etiquetas --}}
            <div class="overflow-x-auto rounded-2xl border border-slate-150">
                <table class="min-w-full divide-y divide-slate-150 text-left text-xs font-semibold text-slate-700">
                    <thead class="bg-slate-50 text-[10px] text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Desde (m²)</th>
                            <th class="px-4 py-3">Hasta (m²)</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 bg-white">
                        @forelse($etiquetas as $e)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 py-3 text-slate-900 font-bold">{{ $e->nombre }}</td>
                                <td class="px-4 py-3">{{ number_format($e->desde, 2) }}</td>
                                <td class="px-4 py-3">{{ number_format($e->hasta, 2) }}</td>
                                <td class="px-4 py-3 text-right flex items-center justify-end gap-1.5">
                                    <button wire:click="editEtiqueta({{ $e->id }})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="deleteEtiqueta({{ $e->id }})" wire:confirm="¿Está seguro de eliminar esta etiqueta? Esto también eliminará el precio asociado." class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="Borrar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">No hay etiquetas creadas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECCIÓN B: PRECIOS POR TAMAÑO --}}
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-6">
            <div class="flex items-center gap-2 pb-4 border-b border-slate-100">
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m-4-6h8"/></svg>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Precios por Tamaño</h2>
            </div>

            {{-- Formulario de Precios --}}
            <form wire:submit.prevent="savePrecio" class="bg-slate-50 p-4 rounded-2xl border border-slate-150 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Etiqueta de Tamaño</label>
                        <select wire:model="precioEtiquetaId" required
                            class="block w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 bg-white font-semibold cursor-pointer">
                            <option value="">Seleccione una etiqueta...</option>
                            @foreach($etiquetas as $e)
                                {{-- Permitir la etiqueta seleccionada al editar --}}
                                @if(!$precios->contains('tamano_etiqueta_id', $e->id) || $precioEtiquetaId == $e->id)
                                    <option value="{{ $e->id }}">{{ $e->nombre }} ({{ number_format($e->desde, 1) }}-{{ number_format($e->hasta, 1) }} m²)</option>
                                @endif
                            @endforeach
                        </select>
                        @error('precioEtiquetaId') <p class="text-red-500 text-[10px]">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Precio Mensual (Bs.)</label>
                        <input type="number" step="0.01" wire:model="precioMensual" required placeholder="0.00"
                            class="block w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 bg-white font-bold text-slate-700">
                        @error('precioMensual') <p class="text-red-500 text-[10px]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    @if($precioId)
                        <button type="button" wire:click="cancelEdit"
                            class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition rounded-lg font-bold text-xs uppercase tracking-wider">
                            Cancelar
                        </button>
                    @endif
                    <button type="submit"
                        class="px-4 py-1.5 bg-slate-900 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs uppercase tracking-wider shadow-sm transition">
                        {{ $precioId ? 'Actualizar' : 'Asignar Precio' }}
                    </button>
                </div>
            </form>

            {{-- Tabla de Precios --}}
            <div class="overflow-x-auto rounded-2xl border border-slate-150">
                <table class="min-w-full divide-y divide-slate-150 text-left text-xs font-semibold text-slate-700">
                    <thead class="bg-slate-50 text-[10px] text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Etiqueta de Tamaño</th>
                            <th class="px-4 py-3">Rango m²</th>
                            <th class="px-4 py-3">Precio Mensual Base</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 bg-white">
                        @forelse($precios as $p)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 py-3 text-slate-900 font-bold">{{ $p->etiqueta?->nombre }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ $p->etiqueta ? number_format($p->etiqueta->desde, 1) . ' - ' . number_format($p->etiqueta->hasta, 1) : 'N/A' }} m²
                                </td>
                                <td class="px-4 py-3 text-indigo-700 font-black">Bs. {{ number_format($p->precio_mensual, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right flex items-center justify-end gap-1.5">
                                    <button wire:click="editPrecio({{ $p->id }})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="deletePrecio({{ $p->id }})" wire:confirm="¿Está seguro de eliminar este precio?" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="Borrar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">No hay precios asignados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- SECCIÓN C: DESCUENTOS POR TIEMPO --}}
    <div class="bg-white rounded-3xl border border-slate-200 p-6 md:p-8 shadow-sm space-y-6">
        <div class="flex items-center gap-2 pb-4 border-b border-slate-100">
            <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0h4m-4 0h-4m0 0v13m0 13h10a2 2 0 002-2V9a2 2 0 00-2-2h-2m-8 14H5a2 2 0 01-2-2V9a2 2 0 012-2h2"/></svg>
            </div>
            <h2 class="text-lg font-black text-slate-900 tracking-tight">Descuentos por Tiempo</h2>
        </div>

        {{-- Formulario de Descuentos --}}
        <form wire:submit.prevent="saveDescuento" class="bg-slate-50 p-4 rounded-2xl border border-slate-150 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Mínima Cantidad de Meses</label>
                    <input type="number" min="1" wire:model="descuentoMinMeses" required placeholder="Ej. 3"
                        class="block w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 bg-white font-bold text-slate-700">
                    @error('descuentoMinMeses') <p class="text-red-500 text-[10px]">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Descuento Global (%)</label>
                    <input type="number" step="0.01" min="0" max="100" wire:model="descuentoGlobal" required placeholder="Ej. 10.00"
                        class="block w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-500 bg-white font-bold text-indigo-600">
                    @error('descuentoGlobal') <p class="text-red-500 text-[10px]">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                @if($descuentoId)
                    <button type="button" wire:click="cancelEdit"
                        class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition rounded-lg font-bold text-xs uppercase tracking-wider">
                        Cancelar
                    </button>
                @endif
                <button type="submit"
                    class="px-4 py-1.5 bg-slate-900 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs uppercase tracking-wider shadow-sm transition">
                    {{ $descuentoId ? 'Actualizar' : 'Agregar Regla' }}
                </button>
            </div>
        </form>

        {{-- Tabla de Descuentos --}}
        <div class="overflow-x-auto rounded-2xl border border-slate-150">
            <table class="min-w-full divide-y divide-slate-150 text-left text-xs font-semibold text-slate-700">
                <thead class="bg-slate-50 text-[10px] text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Mínima Cantidad de Meses</th>
                        <th class="px-4 py-3">Descuento Global Aplicado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 bg-white">
                    @forelse($descuentos as $d)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 text-slate-900 font-bold">Desde {{ $d->min_meses }} meses</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                    {{ number_format($d->descuento, 2) }} %
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right flex items-center justify-end gap-1.5">
                                <button wire:click="editDescuento({{ $d->id }})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button wire:click="deleteDescuento({{ $d->id }})" wire:confirm="¿Está seguro de eliminar esta regla de descuento?" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="Borrar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400 italic">No hay reglas de descuento por tiempo creadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
