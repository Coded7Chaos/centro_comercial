<x-filament-panels::page>
    <div class="mb-4">
        <p class="text-gray-600 dark:text-gray-300">
            Bitácora de cambios sobre los datos del sistema. Filtra por categoría (módulo), acción, usuario que ejecutó la operación, o rango de fechas. Cada entrada permite ver el detalle de los cambios.
        </p>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
