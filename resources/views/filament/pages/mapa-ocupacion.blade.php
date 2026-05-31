<x-filament-panels::page>
    <div class="mb-4">
        <p class="text-gray-600 dark:text-gray-300">
            Este módulo te muestra gráficamente el estado de ocupación de las tiendas distribuidas por cada piso de la infraestructura. Haz clic en cualquiera de los pisos para visualizar la distribución física de sus locales y detalles comerciales en tiempo real.
        </p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($estadisticas as $stat)
            <div wire:click="selectPiso({{ $stat['id'] }})"
                 class="cursor-pointer transition-all duration-300 hover:scale-[1.02] hover:shadow-md fi-wi-stats-overview-stat relative rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 {{ $selectedPisoId === $stat['id'] ? 'ring-2 ring-primary-500 dark:ring-primary-400' : '' }}">
                <div class="flex items-center gap-x-4">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold text-gray-950 dark:text-white">{{ $stat['piso'] }}</h2>
                            @if($selectedPisoId === $stat['id'])
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-400">Activo</span>
                            @endif
                        </div>
                        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>Tiendas Totales:</span> 
                            <span class="font-bold text-gray-900 dark:text-white">{{ $stat['total'] }}</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>Disponibles:</span> 
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $stat['disponibles'] }}</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>Alquiladas:</span> 
                            <span class="font-bold text-danger-600 dark:text-danger-400">{{ $stat['ocupadas'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex items-center gap-x-3">
                    <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700 overflow-hidden">
                        <div class="bg-primary-600 h-3 rounded-full dark:bg-primary-500 transition-all" style="width: {{ $stat['porcentaje'] }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $stat['porcentaje'] }}%</span>
                </div>
            </div>
        @endforeach
        
        @if(count($estadisticas) === 0)
            <div class="col-span-full text-center py-8 text-gray-500">
                Aún no hay pisos registrados en la base de datos.
            </div>
        @endif
    </div>

    @if($selectedPiso)
        <div class="mt-8 space-y-6 pt-6 border-t border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-black text-gray-950 dark:text-white">
                        Distribución y Detalle: {{ $selectedPiso->nombre }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Catálogo de locales comerciales en este nivel con estados de contratos y pérdidas por vacancia estimadas.
                    </p>
                </div>
                <button wire:click="$set('selectedPisoId', null)" class="px-4 py-2 text-xs text-gray-500 hover:text-gray-950 dark:hover:text-white font-bold transition rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                    Cerrar Detalle
                </button>
            </div>

            @if(count($tiendas) === 0)
                <div class="text-center py-12 bg-white dark:bg-gray-900 rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 text-gray-500">
                    No hay locales registrados en este piso.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($tiendas as $tienda)
                        @if($tienda['ocupada'])
                            <!-- Tarjeta Tienda Alquilada -->
                            <div class="relative rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 border-l-4 border-primary-500 dark:bg-gray-900 dark:ring-white/10 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400">
                                            Local {{ $tienda['numero'] }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/30">
                                            Alquilado
                                        </span>
                                    </div>
                                    <h4 class="text-lg font-black text-gray-950 dark:text-white mb-3">
                                        {{ $tienda['nombre'] }}
                                    </h4>
                                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 font-medium">
                                        <div class="flex items-start gap-2">
                                            <span class="text-gray-400">Marcas:</span>
                                            <span class="text-gray-900 dark:text-white font-bold">{{ $tienda['marcas'] }}</span>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <span class="text-gray-400">Encargado:</span>
                                            <span class="text-gray-900 dark:text-white font-semibold">{{ $tienda['cliente'] }}</span>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <span class="text-gray-400">Contacto:</span>
                                            <span class="text-gray-900 dark:text-white font-semibold">{{ $tienda['contacto'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2 text-xs">
                                    <div class="flex justify-between text-gray-500">
                                        <span>Vigencia Contrato:</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $tienda['fecha_inicio'] }} al {{ $tienda['fecha_fin'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-500">
                                        <span>Próximo Pago:</span>
                                        <span class="font-extrabold text-primary-600 dark:text-primary-400">{{ $tienda['fecha_proximo_pago'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Tarjeta Tienda Disponible -->
                            <div class="relative rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 border-l-4 border-emerald-500 dark:bg-gray-900 dark:ring-white/10 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400">
                                            Local {{ $tienda['numero'] }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30">
                                            Disponible
                                        </span>
                                    </div>
                                    <h4 class="text-lg font-black text-gray-950 dark:text-white mb-3">
                                        Disponible para Alquilar
                                    </h4>
                                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 font-medium">
                                        <div class="flex items-start gap-2">
                                            <span class="text-gray-400">Ref. Alquiler:</span>
                                            <span class="text-gray-900 dark:text-white font-bold">Local {{ $tienda['numero'] }}</span>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <span class="text-gray-400">Libre desde:</span>
                                            <span class="text-gray-900 dark:text-white font-semibold">{{ $tienda['fecha_libre_desde'] }}</span>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <span class="text-gray-400">Días Inactivo:</span>
                                            <span class="text-gray-900 dark:text-white font-semibold">{{ $tienda['dias_libre'] }} días</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs">
                                    <div class="flex justify-between items-center text-gray-500">
                                        <span>Pérdida por Vacancia:</span>
                                        <span class="font-extrabold text-danger-600 dark:text-danger-400 text-sm">Bs. {{ number_format($tienda['costo_oportunidad'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
