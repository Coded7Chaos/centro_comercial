<x-filament-panels::page>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        @foreach ($this->pisos as $index => $piso)

            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition">

                {{-- HEADER --}}
                <div class="flex items-center justify-between mb-5">

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Piso {{ $index + 1 }}
                    </h2>

                    <span class="text-xs px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        Configuración
                    </span>

                </div>

                {{-- NOMBRE --}}
                <div class="mb-4">

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nombre del piso
                    </label>

                    <input
                        type="text"
                        wire:model="pisos.{{ $index }}.nombre"
                        class="
                            w-full
                            rounded-lg
                            border-gray-300 dark:border-gray-700
                            bg-white dark:bg-gray-800
                            text-gray-900 dark:text-white
                            focus:border-primary-600
                            focus:ring-2
                            focus:ring-primary-600/30
                            focus:outline-none
                            transition
                        "
                    >

                </div>

                {{-- TIENDAS --}}
                <div class="mb-4">

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Cantidad de tiendas
                    </label>

                    <input
                        type="number"
                        min="0"
                        wire:model="pisos.{{ $index }}.cantidad_tiendas"
                        class="
                            w-full
                            rounded-lg
                            border-gray-300 dark:border-gray-700
                            bg-white dark:bg-gray-800
                            text-gray-900 dark:text-white
                            focus:border-primary-600
                            focus:ring-2
                            focus:ring-primary-600/30
                            focus:outline-none
                            transition
                        "
                    >

                </div>

                {{-- ESTADO --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Estado
                    </label>

                    <select
                        wire:model="pisos.{{ $index }}.estado"
                        class="
                            w-full
                            rounded-lg
                            border-gray-300 dark:border-gray-700
                            bg-white dark:bg-gray-800
                            text-gray-900 dark:text-white
                            focus:border-primary-600
                            focus:ring-2
                            focus:ring-primary-600/30
                            focus:outline-none
                            transition
                        "
                    >
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>

                </div>

            </div>

        @endforeach

    </div>

    {{-- BOTÓN --}}
    <div class="mt-8 flex justify-end">

        <button
            wire:click="guardar"
            class="
                px-6 py-3
                bg-primary-600 hover:bg-primary-700
                text-white font-medium
                rounded-lg
                shadow-sm
                transition
                focus:ring-2 focus:ring-primary-600/30
                focus:outline-none
            "
        >
            Guardar pisos
        </button>

    </div>

</x-filament-panels::page>