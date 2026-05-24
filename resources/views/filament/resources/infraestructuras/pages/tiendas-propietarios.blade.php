<x-filament-panels::page>

    <div class="mb-8">

        <h1 class="text-4xl font-bold">
            Piso {{ $this->piso->nombre }}
        </h1>

        <p class="text-gray-500 mt-2">
            Administración de tiendas
        </p>

    </div>

    <div class="space-y-8">

        @foreach ($this->tiendas as $index => $tienda)
            <div
                class="
                    bg-white
                    rounded-3xl
                    border
                    shadow-sm
                    overflow-hidden
                ">

                {{-- HEADER --}}

                <div
                    class="
                        px-6
                        py-4
                        border-b
                        bg-gray-50
                        flex
                        items-center
                        justify-between
                    ">

                    <div>

                        <h2 class="text-2xl font-bold">
                            Tienda {{ $tienda['numero'] }}
                        </h2>

                        <p class="text-sm text-gray-500">
                            {{ $tienda['nombre'] ?: 'Sin nombre' }}
                        </p>

                    </div>

                </div>

                {{-- BODY --}}

                <div class="p-6">

                    <div
                        class="
                            grid
                            grid-cols-1
                            md:grid-cols-2
                            gap-6
                        ">

                        {{-- CLIENTE --}}

                        <div>

                            <label class="block mb-2 font-semibold">
                                Cliente
                            </label>

                            <select wire:model.live="tiendas.{{ $index }}.cliente_id"
                                class="w-full rounded-2xl border-gray-300">

                                <option value="">
                                    Seleccione cliente
                                </option>

                                @foreach ($this->clientes as $id => $nombre)
                                    <option value="{{ $id }}">
                                        {{ trim($nombre) }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        {{-- NOMBRE TIENDA --}}

                        <div>

                            <label class="block mb-2 font-semibold">
                                Nombre de tienda
                            </label>

                            <input type="text" wire:model="tiendas.{{ $index }}.nombre"
                                placeholder="Ej: Nike Center" class="w-full rounded-2xl border-gray-300">

                        </div>

                        {{-- DESCRIPCIÓN --}}

                        <div class="md:col-span-2">

                            <label class="block mb-2 font-semibold">
                                Descripción
                            </label>

                            <textarea wire:model="tiendas.{{ $index }}.descripcion" rows="3" placeholder="Descripción de la tienda"
                                class="w-full rounded-2xl border-gray-300"></textarea>

                        </div>

                        {{-- MARCA --}}

                        <div>

                            <label class="block mb-2 font-semibold">
                                Marca
                            </label>

                            <select wire:model="tiendas.{{ $index }}.marca_id"
                                class="w-full rounded-2xl border-gray-300">

                                <option value="">
                                    Seleccione marca
                                </option>

                                @foreach ($this->marcas as $id => $nombre)
                                    <option value="{{ $id }}">
                                        {{ $nombre }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        {{-- TAMAÑO --}}

                        <div>

                            <label class="block mb-2 font-semibold">
                                Tamaño
                            </label>

                            <select wire:model="tiendas.{{ $index }}.tamano"
                                class="w-full rounded-2xl border-gray-300">

                                <option value="">
                                    Seleccione tamaño
                                </option>

                                <option value="pequeño">
                                    Pequeño
                                </option>

                                <option value="mediano">
                                    Mediano
                                </option>

                                <option value="grande">
                                    Grande
                                </option>

                            </select>

                        </div>

                        {{-- TELÉFONO REFERENCIA --}}

                        <div>

                            <label class="block mb-2 font-semibold">
                                Número referencia
                            </label>

                            <input type="text" disabled wire:model="tiendas.{{ $index }}.telefono_referencia"
                                class="
            w-full
            rounded-2xl
            border-gray-300
            bg-gray-100
        ">

                        </div>

                        {{-- ESTADO --}}

                        <div>

                            <label class="block mb-2 font-semibold">
                                Estado
                            </label>

                            <select wire:model="tiendas.{{ $index }}.estado"
                                class="w-full rounded-2xl border-gray-300">

                                <option value="activo">
                                    Activo
                                </option>

                                <option value="inactivo">
                                    Inactivo
                                </option>

                                <option value="disponible">
                                    Disponible
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

            </div>
        @endforeach

    </div>

    <div class="mt-8">

        <button wire:click="guardar"
            class="
                px-8
                py-4
                bg-primary-600
                text-white
                rounded-2xl
                font-semibold
            ">

            Guardar información

        </button>

    </div>

</x-filament-panels::page>
