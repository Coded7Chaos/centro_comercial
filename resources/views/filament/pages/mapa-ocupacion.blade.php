<x-filament-panels::page>
    <div class="mb-4">
        <p class="text-gray-600 dark:text-gray-300">Este módulo (Página personalizada pura de Filament) te muestra gráficamente el estado de ocupación de las tiendas distribuidas por cada piso de la infraestructura.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($estadisticas as $stat)
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-4">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-950 dark:text-white">{{ $stat['piso'] }}</h2>
                        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>Tiendas Totales:</span> 
                            <span class="font-bold text-gray-900 dark:text-white">{{ $stat['total'] }}</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>Disponibles:</span> 
                            <span class="font-bold text-success-600 dark:text-success-400">{{ $stat['disponibles'] }}</span>
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
</x-filament-panels::page>
