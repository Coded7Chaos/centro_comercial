@php
    $saldo = $deuda - $pagado;
    $porcentajePagado = $deuda > 0 ? round(($pagado / $deuda) * 100) : 0;
    
    // Status colors
    $barColor = $porcentajePagado >= 100 ? 'bg-green-500' : ($porcentajePagado > 50 ? 'bg-yellow-500' : 'bg-red-500');
    
    $chartPagado = $pagado;
    $chartPendiente = max(0, $saldo);
@endphp

<div class="space-y-6 pb-2">
    <!-- Resumen Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900 shadow-sm relative overflow-hidden">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto Total del Contrato</div>
            <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">Bs. {{ number_format($deuda, 2) }}</div>
        </div>
        
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900 shadow-sm relative overflow-hidden">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pagado</div>
            <div class="text-xl font-bold text-green-600 mt-1">Bs. {{ number_format($pagado, 2) }}</div>
        </div>
        
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900 shadow-sm relative overflow-hidden">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Saldo Pendiente</div>
            <div class="text-xl font-bold text-red-600 mt-1">Bs. {{ number_format(max(0, $saldo), 2) }}</div>
        </div>
    </div>

    <!-- GRÁFICAS INTEGRADAS (Chart.js via Alpine) -->
    <div 
        x-data="{
            init() {
                if (typeof Chart === 'undefined') {
                    let script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                    script.onload = () => this.renderChart();
                    document.head.appendChild(script);
                } else {
                    this.renderChart();
                }
            },
            renderChart() {
                let self = this;
                setTimeout(function() {
                    let ctx = self.$refs.canvas;
                    if(ctx) {
                        const permanentLabelsPlugin = (window.filamentChartJsGlobalPlugins && window.filamentChartJsGlobalPlugins.find(function(p) { return p.id === 'permanentLabels'; })) || {
                            id: 'permanentLabels',
                            afterDatasetsDraw(chart) {
                                const { ctx } = chart;
                                ctx.save();
                                chart.data.datasets.forEach(function(dataset, i) {
                                    const meta = chart.getDatasetMeta(i);
                                    if (meta.hidden) return;
                                    meta.data.forEach(function(element, index) {
                                        const dataValue = dataset.data[index];
                                        if (dataValue === 0 || dataValue === null || dataValue === undefined) return;
                                        
                                        if (['pie', 'doughnut'].includes(chart.config.type)) {
                                            ctx.font = 'bold 10px Inter, sans-serif';
                                            let sum = dataset.data.reduce(function(a, b) { return a + Number(b); }, 0);
                                            let percentage = (sum === 0) ? '0%' : (dataValue * 100 / sum).toFixed(1) + '%';
                                            let textLine1 = 'Bs. ' + Number(dataValue).toLocaleString();
                                            let textLine2 = '(' + percentage + ')';
                                            const center = element.tooltipPosition();
                                            
                                            ctx.save();
                                            let maxW = Math.max(ctx.measureText(textLine1).width, ctx.measureText(textLine2).width);
                                            let w = maxW + 12;
                                            let h = 28;
                                            let r = 6;
                                            ctx.beginPath();
                                            ctx.moveTo(center.x - w/2 + r, center.y - h/2);
                                            ctx.lineTo(center.x + w/2 - r, center.y - h/2);
                                            ctx.quadraticCurveTo(center.x + w/2, center.y - h/2, center.x + w/2, center.y - h/2 + r);
                                            ctx.lineTo(center.x + w/2, center.y + h/2 - r);
                                            ctx.quadraticCurveTo(center.x + w/2, center.y + h/2, center.x + w/2 - r, center.y + h/2);
                                            ctx.lineTo(center.x - w/2 + r, center.y + h/2);
                                            ctx.quadraticCurveTo(center.x - w/2, center.y + h/2, center.x - w/2, center.y + h/2 - r);
                                            ctx.lineTo(center.x - w/2, center.y - h/2 + r);
                                            ctx.quadraticCurveTo(center.x - w/2, center.y - h/2, center.x - w/2 + r, center.y - h/2);
                                            ctx.closePath();
                                            ctx.fillStyle = '#0f172a';
                                            ctx.fill();
                                            ctx.restore();
                                            
                                            ctx.fillStyle = '#ffffff';
                                            ctx.textAlign = 'center';
                                            ctx.textBaseline = 'middle';
                                            ctx.fillText(textLine1, center.x, center.y - 6);
                                            ctx.fillText(textLine2, center.x, center.y + 6);
                                        }
                                    });
                                });
                                ctx.restore();
                            }
                        };

                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Monto Pagado', 'Falta Pagar'],
                                datasets: [{
                                    data: [{{ $chartPagado }}, {{ $chartPendiente }}],
                                    backgroundColor: ['#16a34a', '#dc2626'],
                                    borderWidth: 0,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom', labels: { color: '#9ca3af', font: { size: 11 } } }
                                },
                                cutout: '75%'
                            },
                            plugins: [permanentLabelsPlugin]
                        });
                    }
                }, 150);
            }
        }"
        class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-white/5"
    >
        <div class="border-b border-gray-200 md:border-b-0 md:border-r md:border-gray-200 dark:border-gray-700 pb-4 md:pb-0 md:pr-4">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-2 text-center uppercase tracking-widest">Gráfica de Pagos</h3>
            <div class="relative h-40 w-full flex justify-center">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
        
        <div class="pt-4 md:pt-0 md:pl-4">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-2 text-center uppercase tracking-widest">Rendimiento</h3>
            <div class="flex flex-col items-center justify-center h-full pb-4">
                <div class="text-5xl font-black {{ $porcentajePagado >= 100 ? 'text-green-500' : 'text-blue-500' }}">{{ $porcentajePagado }}%</div>
                <div class="text-xs text-gray-500 mt-1 font-medium">Monto total cancelado</div>
                
                <div class="w-full h-3 bg-gray-200 rounded-full mt-4 dark:bg-gray-700 overflow-hidden relative shadow-inner max-w-[150px]">
                    <div class="h-3 rounded-full {{ $barColor }} transition-all" style="width: {{ $porcentajePagado > 100 ? 100 : $porcentajePagado }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de cobros generados -->
    <div>
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Historial de Cobros al Inquilino</h3>
        <div class="border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Concepto / Mes</th>
                        <th class="px-4 py-2 font-semibold text-right">Monto (Bs)</th>
                        <th class="px-4 py-2 font-semibold text-center">Fecha Pago</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado de Cobro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @php
                        $suscripcionesIds = \App\Models\Suscripciones::where('cliente_id', $record->cliente_id)
                            ->where('infraestructuras_tienda_id', $record->infraestructuras_tienda_id)
                            ->pluck('id');

                        $cobros = \App\Models\SuscripcionesCobros::whereIn('suscripcion_id', $suscripcionesIds)
                            ->with(['pagos', 'suscripcion.infraestructurasTienda'])
                            ->get()
                            ->sortBy(function ($c) {
                                return [$c->suscripcion->fecha_inicio, $c->fecha_vencimiento];
                            });

                        $prevSubId = null;
                        $monthCounter = 0;
                    @endphp

                    @forelse($cobros as $cobro)
                        @php
                            if ($cobro->suscripcion_id !== $prevSubId) {
                                $monthCounter = 1;
                                $prevSubId = $cobro->suscripcion_id;
                            } else {
                                $monthCounter++;
                            }

                            $tiendaNombre = $cobro->suscripcion->infraestructurasTienda->nombre ?? 'Sin tienda';
                            $hasGuarantee = (str_contains(strtolower($cobro->concepto), 'garantía') || str_contains(strtolower($cobro->concepto), 'garantia'));

                            if ($monthCounter === 1) {
                                if ($hasGuarantee) {
                                    $montoAlquiler = $cobro->monto / 2;
                                    $conceptoFormatted = "Pago inicial (Bs. " . number_format($montoAlquiler, 2) . ") + Garantía (Bs. " . number_format($montoAlquiler, 2) . ") - " . $tiendaNombre;
                                } else {
                                    $conceptoFormatted = "Pago inicial - " . $tiendaNombre;
                                }
                            } else {
                                $conceptoFormatted = "Cobro Mensual #" . $monthCounter . " - " . $tiendaNombre;
                            }

                            // Payment date
                            $fechaPagoRaw = $cobro->pagos->max('fecha_pago');
                            $fechaPagoFormatted = $fechaPagoRaw ? \Carbon\Carbon::parse($fechaPagoRaw)->format('d/m/Y') : '—';

                            // Real state
                            $estadoReal = $cobro->estado;
                            if ($estadoReal === 'pendiente' && now()->toDateString() > $cobro->fecha_vencimiento) {
                                $estadoReal = 'vencido';
                            }

                            if ($estadoReal === 'pagado') {
                                $labelText = 'PAGADO';
                                $badgeClass = 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400';
                            } elseif ($estadoReal === 'parcial') {
                                $labelText = 'PAGO PARCIAL';
                                $badgeClass = 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400';
                            } elseif ($estadoReal === 'vencido') {
                                $labelText = 'ATRASADO';
                                $badgeClass = 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400';
                            } else {
                                $labelText = 'COBRO PROGRAMADO';
                                $badgeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400';
                            }
                        @endphp
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3 font-medium text-xs md:text-sm">{{ $conceptoFormatted }}</td>
                            <td class="px-4 py-3 text-right font-bold text-xs md:text-sm">Bs. {{ number_format($cobro->monto, 2) }}</td>
                            <td class="px-4 py-3 text-center text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ $fechaPagoFormatted }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center rounded-md px-2 py-1 text-[10px] md:text-[11px] font-bold {{ $badgeClass }}">
                                    {{ $labelText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 text-xs italic">Este inquilino es nuevo y aún no tiene cobros generados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
