<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renovar Contrato de Alquiler - Administración</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .card-hud {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 p-6 md:p-12">

    <div class="max-w-4xl mx-auto space-y-8" x-data="{
        tiendaId: '{{ $tienda->id }}',
        duracionValor: {{ old('duracion_valor', 1) }},
        duracionUnidad: '{{ old('duracion_unidad', 'meses') }}',
        fechaInicio: '{{ old('fecha_inicio', $defaultStartDate) }}',
        precioMensualBase: 0,
        descuentoPorcentaje: 0,
        descuentoMontoMensual: 0,
        precioMensualConDescuento: 0,
        precioTotalSinDescuento: 0,
        precioTotalConDescuento: 0,
        pagoInicialMonto: 0,
        etiqueta: '—',
        tamano: 0,
        
        get totalMonths() {
            return this.duracionUnidad === 'años' ? this.duracionValor * 12 : this.duracionValor;
        },
        get fechaFin() {
            if (!this.fechaInicio) return '—';
            let parts = this.fechaInicio.split('-');
            let start = new Date(parts[0], parts[1] - 1, parts[2]);
            start.setMonth(start.getMonth() + parseInt(this.totalMonths));
            start.setDate(start.getDate() - 1);
            
            let y = start.getFullYear();
            let m = String(start.getMonth() + 1).padStart(2, '0');
            let d = String(start.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },
        
        async updateTienda() {
            if (!this.tiendaId) return;
            let url = `/admin/suscripciones-custom/tienda-precio/${this.tiendaId}?duracion_valor=${this.duracionValor}&duracion_unidad=${this.duracionUnidad}`;
            let res = await fetch(url);
            let data = await res.json();
            this.precioMensualBase = data.precio_mensual_base;
            this.descuentoPorcentaje = data.descuento_porcentaje;
            this.descuentoMontoMensual = data.descuento_monto_mensual;
            this.precioMensualConDescuento = data.precio_mensual_con_descuento;
            this.precioTotalSinDescuento = data.precio_total_sin_descuento;
            this.precioTotalConDescuento = data.precio_total_con_descuento;
            // Renewal initial payment is only 1 month rent (no security deposit needed)
            this.pagoInicialMonto = data.precio_mensual_con_descuento;
            this.etiqueta = data.etiqueta;
            this.tamano = data.tamano;
        }
    }" x-init="updateTienda()">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                    <span class="px-3 py-1 bg-amber-500 text-slate-900 rounded-lg text-xs font-black uppercase tracking-wider">Renovación</span>
                    Renovar Contrato de Alquiler
                </h1>
                <p class="text-sm text-slate-500 font-medium mt-1">Paso 1: Configurar fechas y nueva duración del arrendamiento</p>
            </div>
            <a href="/admin/suscripciones" 
                class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Cancelar
            </a>
        </div>

        {{-- FORM CARD --}}
        <form action="{{ route('admin.suscripciones.guardar-renovacion', $parentSub->id) }}" method="POST" class="card-hud p-6 md:p-10 space-y-8">
            @csrf

            {{-- FIXED INFORMATION GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-200">
                <div class="space-y-1">
                    <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Cliente (Inquilino)</span>
                    <span class="block text-sm font-bold text-slate-800">
                        {{ $cliente->nombre_completo }}
                    </span>
                    <span class="block text-xs text-slate-500">CI: {{ $cliente->ci }} • Celular: {{ $cliente->numero_celular }}</span>
                </div>
                <div class="space-y-1 border-t md:border-t-0 md:border-l border-slate-200 pt-4 md:pt-0 md:pl-6">
                    <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Local Comercial (Fijo)</span>
                    <span class="block text-sm font-bold text-slate-800">
                        Local N° {{ $tienda->numero }} — {{ $tienda->nombre ?: 'Sin nombre' }}
                    </span>
                    <span class="block text-xs text-slate-500">Piso: {{ $tienda->piso?->nombre }} • Dimensión: {{ $tienda->tamano }}m²</span>
                </div>
            </div>

            {{-- DURATION & START DATE GROUP --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- DURACIÓN VALOR --}}
                <div class="space-y-2">
                    <label for="duracion_valor" class="block text-xs font-black uppercase tracking-wider text-slate-500">Nueva Duración <span class="text-red-500">*</span></label>
                    <div class="flex rounded-xl shadow-sm">
                        <input type="number" name="duracion_valor" id="duracion_valor" min="1" required x-model="duracionValor" @input="updateTienda()"
                            class="block w-full border border-slate-200 rounded-l-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 text-center font-bold">
                        <select name="duracion_unidad" x-model="duracionUnidad" @change="updateTienda()"
                            class="border border-l-0 border-slate-200 rounded-r-xl px-4 py-3 text-sm bg-slate-100 font-bold text-slate-700 cursor-pointer">
                            <option value="meses">Meses</option>
                            <option value="años">Años</option>
                        </select>
                    </div>
                    @error('duracion_valor') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- FECHA INICIO --}}
                <div class="space-y-2">
                    <label for="fecha_inicio" class="block text-xs font-black uppercase tracking-wider text-slate-500">Fecha de Inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" required x-model="fechaInicio"
                        class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                    <p class="text-[9px] text-amber-600 font-bold mt-1">Sugerido: Día posterior al fin de contrato previo</p>
                    @error('fecha_inicio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- FECHA FIN (READ-ONLY) --}}
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400">Fecha de Vencimiento (Calculada)</label>
                    <div class="block w-full border border-slate-100 rounded-xl px-4 py-3 text-sm bg-slate-100 font-semibold text-slate-500 shadow-inner" 
                         x-text="fechaFin ? fechaFin.split('-').reverse().join('/') : '—'"></div>
                </div>

            </div>

            {{-- DYNAMIC CALCULATIONS & BREAKDOWN --}}
            <div class="border-t border-slate-100 pt-8 space-y-6">
                <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Resumen y Desglose Financiero
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-150">
                    <div class="space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Alquiler Mensual</div>
                        <div class="flex flex-col gap-0.5">
                            <template x-if="descuentoPorcentaje > 0">
                                <span class="text-xs line-through text-slate-400">Regular: Bs. <span x-text="Number(precioMensualBase).toFixed(2)"></span></span>
                            </template>
                            <div class="text-2xl font-black text-slate-900">Bs. <span x-text="Number(precioMensualConDescuento).toFixed(2)"></span></div>
                        </div>
                        <template x-if="descuentoPorcentaje > 0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-red-100 text-red-800 mt-1">
                                Descuento: -<span x-text="Number(descuentoPorcentaje)"></span>% (Bs. -<span x-text="Number(descuentoMontoMensual).toFixed(2)"></span>)
                            </span>
                        </template>
                        <div class="text-[10px] text-slate-500 font-medium pt-1" x-text="'Categoría: ' + etiqueta"></div>
                    </div>
                    <div class="space-y-1 border-t md:border-t-0 md:border-x border-slate-200 py-4 md:py-0 md:px-6">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Precio Total Renovación</div>
                        <div class="flex flex-col gap-0.5">
                            <template x-if="descuentoPorcentaje > 0">
                                <span class="text-xs line-through text-slate-400">Regular: Bs. <span x-text="Number(precioTotalSinDescuento).toFixed(2)"></span></span>
                            </template>
                            <div class="text-2xl font-black text-indigo-700">Bs. <span x-text="Number(precioTotalConDescuento).toFixed(2)"></span></div>
                        </div>
                        <div class="text-[10px] text-slate-500 font-medium pt-1" x-text="'Monto neto de renta por ' + totalMonths + ' meses'"></div>
                    </div>
                    <div class="space-y-1 md:pl-6">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pago Inicial Renovación</div>
                        <div class="text-2xl font-black text-emerald-700">Bs. <span x-text="Number(pagoInicialMonto).toFixed(2)"></span></div>
                        <div class="text-[10px] text-slate-500 font-medium pt-1">1 mes de alquiler adelanto (Sin depósito)</div>
                    </div>
                </div>

                {{-- DETAILED PAYMENT METHOD DESCRIPTIVE --}}
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 space-y-3">
                    <div class="flex items-start gap-2.5 text-xs text-amber-900 font-medium">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <div class="space-y-1">
                            <span class="font-black uppercase tracking-wider block text-[10px] text-amber-700">Garantía Transferida</span>
                            <p class="text-pretty">Al tratarse de una **Renovación de Contrato**, la garantía provista originalmente para el Local N° {{ $tienda->numero }} se transfiere automáticamente a este nuevo periodo contractual. Por ende, **no se requiere abonar un nuevo depósito de garantía**, y el monto inicial a pagar equivale únicamente a un (1) mes de renta (Bs. <span class="font-bold" x-text="Number(precioMensualConDescuento).toFixed(2)"></span>).</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUBMIT BUTTONS --}}
            <div class="flex items-center justify-between border-t border-slate-100 pt-8">
                <span class="text-xs text-slate-400 font-medium">Todos los campos marcados con (*) son obligatorios.</span>
                <button type="submit"
                    class="px-8 py-3.5 bg-slate-900 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition duration-300 shadow-md flex items-center gap-2 cursor-pointer">
                    Guardar y descargar contrato
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2h2m3-4H9m1.5 4h1.5m1 1l-1 1m-1-1l1 1m-6-1h.01M9 16h.01"/></svg>
                </button>
            </div>

        </form>
    </div>

</body>
</html>
