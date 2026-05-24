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
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Facturado</div>
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
                setTimeout(() => {
                    let ctx = this.$refs.canvas;
                    if(ctx) {
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
                            }
                        });
                    }
                }, 150);
            }
        }"
        class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-white/5"
    >
        <div class="border-b border-gray-200 md:border-b-0 md:border-r md:border-gray-200 dark:border-gray-700 pb-4 md:pb-0 md:pr-4">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-2 text-center uppercase tracking-widest">Gráfica de Deuda</h3>
            <div class="relative h-40 w-full flex justify-center">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
        
        <div class="pt-4 md:pt-0 md:pl-4">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 mb-2 text-center uppercase tracking-widest">Rendimiento</h3>
            <div class="flex flex-col items-center justify-center h-full pb-4">
                <div class="text-5xl font-black {{ $porcentajePagado >= 100 ? 'text-green-500' : 'text-blue-500' }}">{{ $porcentajePagado }}%</div>
                <div class="text-xs text-gray-500 mt-1 font-medium">Deuda total cancelada</div>
                
                <div class="w-full h-3 bg-gray-200 rounded-full mt-4 dark:bg-gray-700 overflow-hidden relative shadow-inner max-w-[150px]">
                    <div class="h-3 rounded-full {{ $barColor }} transition-all" style="width: {{ $porcentajePagado > 100 ? 100 : $porcentajePagado }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de últimos cobros generados -->
    <div>
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Historial Reciente de Cobros al Inquilino</h3>
        <div class="border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Concepto / Mes</th>
                        <th class="px-4 py-2 font-semibold text-right">Monto (Bs)</th>
                        <th class="px-4 py-2 font-semibold text-center">Estado de Cobro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($record->cobros->sortByDesc('created_at')->take(5) as $cobro)
                        <tr class="bg-white dark:bg-gray-900">
                            <td class="px-4 py-3 font-medium">{{ $cobro->concepto ?? 'Cobro de Alquiler' }}</td>
                            <td class="px-4 py-3 text-right font-bold">Bs. {{ number_format($cobro->monto, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if(strtolower($cobro->estado) === 'pagado')
                                    <span class="inline-flex items-center justify-center rounded-md bg-green-100 px-2 py-1 text-[11px] font-bold text-green-700 w-20">PAGADO</span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-md bg-red-100 px-2 py-1 text-[11px] font-bold text-red-700 w-20">PENDIENTE</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500 text-xs italic">Este inquilino es nuevo y aún no tiene cobros generados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
