<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-wrap items-center justify-between gap-4">

            {{-- Etiqueta --}}
            <div class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300">
                <x-heroicon-o-calendar-days class="w-4 h-4 text-amber-500" />
                <span>Filtrando pagos de</span>
                <span class="text-amber-600 dark:text-amber-400 font-black">{{ $mesNombreActual }}</span>
            </div>

            {{-- Navegador --}}
            <div class="flex items-center gap-2">

                {{-- Flecha izquierda --}}
                <button
                    wire:click="previousMonth"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-amber-50 hover:border-amber-400 hover:text-amber-600 transition-all shadow-sm"
                    title="Mes anterior"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Dropdown de mes --}}
                <select
                    wire:model.live="mes"
                    class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-800 dark:text-gray-100 focus:border-amber-400 focus:ring-0 focus:outline-none shadow-sm cursor-pointer"
                >
                    @foreach($meses as $num => $nombre)
                        <option value="{{ $num }}" @selected($num == $mes)>{{ $nombre }}</option>
                    @endforeach
                </select>

                {{-- Dropdown de año --}}
                <select
                    wire:model.live="anio"
                    class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-800 dark:text-gray-100 focus:border-amber-400 focus:ring-0 focus:outline-none shadow-sm cursor-pointer"
                >
                    @foreach($anios as $y)
                        <option value="{{ $y }}" @selected($y == $anio)>{{ $y }}</option>
                    @endforeach
                </select>

                {{-- Flecha derecha --}}
                <button
                    wire:click="nextMonth"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-amber-50 hover:border-amber-400 hover:text-amber-600 transition-all shadow-sm"
                    title="Mes siguiente"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- Botón "Hoy" --}}
                <button
                    wire:click="currentMonth"
                    class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-amber-50 hover:border-amber-400 hover:text-amber-600 transition-all shadow-sm"
                    title="Ir al mes actual"
                >
                    Hoy
                </button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
