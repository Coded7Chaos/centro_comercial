<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripciones y Tarifas - {{ \App\Models\Infraestructuras::first()?->nombre ?? 'Centro Comercial' }}</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        
        .glass-hud {
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
        }

        .tariff-card {
            transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .tariff-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -15px rgba(15,23,42,0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body class="overflow-x-hidden min-h-screen w-full select-none text-slate-900">

    {{-- MAIN BACKGROUND GRADIENT --}}
    <div class="fixed inset-0 z-0 bg-gradient-to-b from-[#e2e8f0] via-[#cbd5e1] to-[#94a3b8]"></div>

    <x-public-navbar />

    <main class="relative z-10 max-w-7xl mx-auto px-6 pt-32 pb-24">
        
        {{-- BACK TO MALL BUTTON --}}
        <div class="flex justify-center mb-8">
            <a href="/"
                class="inline-flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-white/70 border border-white/50 shadow-lg hover:shadow-xl hover:bg-white/90 transition-all duration-300">
                <div class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-900 text-white shadow-md">
                    <x-heroicon-o-building-office-2 class="w-5 h-5" />
                </div>
                <div class="text-left">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Volver al inicio</div>
                    <div class="font-black text-slate-900 text-xs">{{ \App\Models\Infraestructuras::first()?->nombre ?? 'Centro Comercial' }}</div>
                </div>
            </a>
        </div>

        {{-- HERO HEADER --}}
        <div class="flex flex-col items-center text-center space-y-4 mb-12">
            <span class="px-4 py-1.5 rounded-full bg-slate-900/10 border border-slate-900/10 text-slate-800 text-[10px] font-black tracking-widest uppercase">
                Simulador Tarifario
            </span>
            <h1 class="text-4xl md:text-6xl font-black tracking-tighter text-slate-900 leading-none">
                Calculadora de <span class="text-indigo-700">Alquileres</span>
            </h1>
            <p class="max-w-2xl text-slate-600 font-medium text-sm md:text-base">
                Simula en tiempo real el canon de arrendamiento comercial ingresando las dimensiones de tu local y la duración del contrato comercial.
            </p>
        </div>

        {{-- INTERACTIVE CALCULATOR --}}
        <div x-data="{
            tamanos: @js($tamanos),
            descuentos: @js($descuentos),
            tiendas: @js($tiendas),
            selectedTiendaId: {{ $tiendaId ?? 'null' }},
            adminPhone: @js($adminPhone),
            tamano: {{ $initialTamano }},
            duracionValor: 6,
            duracionUnidad: 'meses',
            garantia: null,

            get selectedTienda() {
                if (! this.selectedTiendaId) return null;
                return this.tiendas.find(t => t.id === this.selectedTiendaId) || null;
            },

            selectTienda(id) {
                this.selectedTiendaId = id ? parseInt(id) : null;
                let t = this.selectedTienda;
                if (t && t.tamano > 0) this.tamano = t.tamano;
            },

            get meses() {
                return this.duracionUnidad === 'años' ? parseInt(this.duracionValor || 0) * 12 : parseInt(this.duracionValor || 0);
            },

            get calculo() {
                let t = parseFloat(this.tamano || 0);
                let m = this.meses;
                
                if (t <= 0 || m <= 0) {
                    return {
                        etiqueta: '—',
                        precio_mensual_base: 0.00,
                        descuento_porcentaje: 0.00,
                        descuento_monto_mensual: 0.00,
                        precio_mensual_con_descuento: 0.00,
                        precio_total_sin_descuento: 0.00,
                        precio_total_con_descuento: 0.00,
                    };
                }
                
                // 1. Encontrar etiqueta de tamaño
                let etiqueta = this.tamanos.find(x => t >= x.desde && t <= x.hasta);
                let etiquetaNombre = etiqueta ? etiqueta.nombre : 'Sin Categoría';
                
                // 2. Encontrar precio base mensual
                let precioMensualBase = etiqueta ? etiqueta.precio_mensual : 0.00;
                
                // 3. Encontrar descuento según meses
                let desc = 0.00;
                let applicableDesc = [...this.descuentos]
                    .reverse()
                    .find(x => m >= x.min_meses);
                if (applicableDesc) {
                    desc = applicableDesc.descuento;
                }
                
                // 4. Realizar cálculos
                let descuentoMontoMensual = (precioMensualBase * desc) / 100;
                let precioMensualConDescuento = precioMensualBase - descuentoMontoMensual;
                let precioTotalSinDescuento = precioMensualBase * m;
                let precioTotalConDescuento = precioMensualConDescuento * m;
                
                return {
                    etiqueta: etiquetaNombre,
                    precio_mensual_base: precioMensualBase,
                    descuento_porcentaje: desc,
                    descuento_monto_mensual: descuentoMontoMensual,
                    precio_mensual_con_descuento: precioMensualConDescuento,
                    precio_total_sin_descuento: precioTotalSinDescuento,
                    precio_total_con_descuento: precioTotalConDescuento,
                };
            },

            get garantiaEfectiva() {
                return this.garantia !== null && this.garantia !== ''
                    ? parseFloat(this.garantia)
                    : parseFloat(this.calculo.precio_mensual_con_descuento);
            },

            get precioTotalConGarantia() {
                return parseFloat(this.calculo.precio_mensual_con_descuento) * this.meses
                    + this.garantiaEfectiva;
            },

            get whatsappUrl() {
                let phone = ((this.selectedTienda?.telefono || this.adminPhone || '') + '').replace(/\D/g, '');
                let tienda = this.selectedTienda;
                let localInfo = tienda
                    ? `\n- Local: ${tienda.nombre} (N° ${tienda.numero})`
                    : '';
                let msg = `Hola, estoy interesado en alquilar un local comercial. Usé el simulador con los siguientes detalles:${localInfo}
- Tamaño: ${this.tamano} m² (${this.calculo.etiqueta})
- Duración: ${this.duracionValor} ${this.duracionUnidad}
- Pago Mensual Estimado: Bs. ${parseFloat(this.calculo.precio_mensual_con_descuento).toFixed(2)}
- Garantía Inicial: Bs. ${parseFloat(this.garantiaEfectiva).toFixed(2)}
- Precio Estimado Total: Bs. ${parseFloat(this.precioTotalConGarantia).toFixed(2)}`;
                return `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
            }
        }" class="space-y-12">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 max-w-5xl mx-auto items-stretch">
                {{-- INPUT PANEL --}}
                <div class="lg:col-span-7 glass-hud rounded-[2.5rem] border border-white/30 bg-white/10 p-8 flex flex-col justify-between space-y-6 shadow-xl">
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-900/10">
                            <div class="h-10 w-10 rounded-xl bg-slate-900/5 flex items-center justify-center text-slate-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-950 text-base leading-tight">Parámetros del Espacio</h3>
                                <p class="text-xs text-slate-500 font-semibold">Configura las dimensiones y periodo temporal.</p>
                            </div>
                        </div>

                        {{-- DROPDOWN: LOCAL --}}
                        <template x-if="tiendas.length > 0">
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600">Local a Cotizar</label>
                                <div class="relative">
                                    <select
                                        :value="selectedTiendaId"
                                        @change="selectTienda($event.target.value)"
                                        class="w-full px-5 py-4 rounded-2xl bg-white/60 border border-white/50 focus:border-indigo-600 focus:bg-white focus:outline-none transition font-bold text-slate-900 shadow-inner appearance-none cursor-pointer pr-10"
                                    >
                                        <option value="">— Elegir local disponible —</option>
                                        <template x-for="t in tiendas" :key="t.id">
                                            <option :value="t.id" :selected="t.id === selectedTiendaId"
                                                    x-text="`${t.nombre} - ${t.tamano} m²`">
                                            </option>
                                        </template>
                                    </select>
                                    <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                                <template x-if="selectedTienda">
                                    <p class="text-[11px] text-slate-500 font-semibold pl-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>El tamaño se actualiza automáticamente al seleccionar un local.</span>
                                    </p>
                                </template>
                            </div>
                        </template>


                        {{-- INPUT: DURACIÓN --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600">Duración del Contrato</label>
                                <input type="number" min="1" x-model.number="duracionValor" 
                                    class="w-full px-5 py-4 rounded-2xl bg-white/60 border border-white/50 focus:border-indigo-600 focus:bg-white focus:outline-none transition font-bold text-slate-900 shadow-inner" 
                                    placeholder="Ej. 6">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600">Unidad de Tiempo</label>
                                <select x-model="duracionUnidad" 
                                    class="w-full px-5 py-4 rounded-2xl bg-white/60 border border-white/50 focus:border-indigo-600 focus:bg-white focus:outline-none transition font-bold text-slate-900 shadow-inner appearance-none cursor-pointer">
                                    <option value="meses">Meses</option>
                                    <option value="años">Años</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- DYNAMIC INFOTEXT --}}
                    <div class="pt-4 border-t border-slate-900/10 text-xs font-semibold text-slate-500 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>El sistema aplica descuentos automáticos según la duración seleccionada.</span>
                    </div>
                </div>

                {{-- RESULTS PANEL --}}
                <div class="lg:col-span-5 bg-slate-900 text-white rounded-[2.5rem] p-8 flex flex-col justify-between shadow-2xl relative overflow-hidden border border-slate-800">
                    <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="space-y-6 relative z-10">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Detalle de Cotización</span>
                            <span class="px-2.5 py-1 rounded-md bg-indigo-600 text-white text-[9px] font-black uppercase tracking-widest" x-text="calculo.etiqueta"></span>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-400">Descuento Aplicado</span>
                                <span class="font-bold text-sm text-green-400" x-text="parseFloat(calculo.descuento_porcentaje).toFixed(2) + '%'"></span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-400">Pago Mensual Estimado</span>
                                <span class="font-bold text-sm text-indigo-400" x-text="'Bs. ' + parseFloat(calculo.precio_mensual_con_descuento).toFixed(2)"></span>
                            </div>

                            <div class="pt-4 border-t border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-400">Garantía Inicial</span>
                                    <span class="text-[10px] text-slate-500 italic">editable</span>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">Bs.</span>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        x-model="garantia"
                                        :placeholder="parseFloat(calculo.precio_mensual_con_descuento).toFixed(2)"
                                        class="w-full pl-10 pr-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 focus:border-indigo-500 focus:outline-none text-sm font-bold text-white placeholder-slate-500 transition"
                                    >
                                </div>
                                <p class="text-[10px] text-slate-500 italic">
                                    Por defecto equivale al pago mensual. Modifica si es necesario.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 space-y-6 relative z-10">
                        <div class="bg-slate-950/50 rounded-2xl p-5 border border-slate-800 shadow-inner">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Precio Estimado Total</div>
                            <div class="text-3xl font-black text-indigo-400 tracking-tight" x-text="'Bs. ' + parseFloat(precioTotalConGarantia).toFixed(2)"></div>
                            <div class="text-[10px] text-slate-500 mt-1.5">
                                Pago mensual × duración + garantía inicial
                            </div>
                        </div>

                        <a :href="whatsappUrl" 
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-full py-4 rounded-2xl bg-white text-slate-900 hover:bg-indigo-600 hover:text-white transition duration-300 font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 shadow-lg shadow-white/10">
                            Consultar oferta
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- TIERS INFORMATION AREA --}}
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 pt-8">
                
                {{-- SIZE TIERS --}}
                <div class="glass-hud rounded-[2rem] p-6 border border-white/20 bg-white/5 space-y-4">
                    <h4 class="font-black text-slate-950 text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        Tramos y Categorías de Tamaño
                    </h4>
                    <div class="overflow-hidden rounded-xl border border-slate-900/10">
                        <table class="w-full text-left text-xs font-semibold text-slate-600 bg-white/40">
                            <thead class="bg-slate-900 text-white font-black">
                                <tr>
                                    <th class="px-4 py-2.5">Categoría</th>
                                    <th class="px-4 py-2.5">Rango (m²)</th>
                                    <th class="px-4 py-2.5 text-right">Precio Base / mes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/5">
                                @foreach($tamanos as $tam)
                                    <tr>
                                        <td class="px-4 py-2.5 font-bold text-slate-900">{{ $tam['nombre'] }}</td>
                                        <td class="px-4 py-2.5">{{ number_format($tam['desde'], 1) }} - {{ number_format($tam['hasta'], 1) }} m²</td>
                                        <td class="px-4 py-2.5 text-right font-black text-indigo-700">Bs. {{ number_format($tam['precio_mensual'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TIME DISCOUNTS --}}
                <div class="glass-hud rounded-[2rem] p-6 border border-white/20 bg-white/5 space-y-4">
                    <h4 class="font-black text-slate-950 text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Descuentos por Permanencia (Meses)
                    </h4>
                    <div class="overflow-hidden rounded-xl border border-slate-900/10">
                        <table class="w-full text-left text-xs font-semibold text-slate-600 bg-white/40">
                            <thead class="bg-slate-900 text-white font-black">
                                <tr>
                                    <th class="px-4 py-2.5">Duración Mínima</th>
                                    <th class="px-4 py-2.5 text-right">Descuento (%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/5">
                                @forelse($descuentos as $desc)
                                    <tr>
                                        <td class="px-4 py-2.5 font-bold text-slate-900">{{ $desc['min_meses'] }} meses</td>
                                        <td class="px-4 py-2.5 text-right font-black text-green-700">{{ number_format($desc['descuento'], 2) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-4 text-center italic text-slate-500">No hay descuentos configurados en este periodo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </main>

    {{-- FOOTER --}}
    <footer class="relative z-10 max-w-7xl mx-auto px-6 py-12 border-t border-slate-900/10 mt-12">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center shadow-md">
                    <span class="text-white font-black text-xl">M</span>
                </div>
                <span class="font-black text-xl tracking-tighter text-slate-900">{{ \App\Models\Infraestructuras::first()?->nombre ?? 'Mall' }}</span>
            </div>
            <p class="text-slate-500 text-xs font-semibold">© 2026. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>
