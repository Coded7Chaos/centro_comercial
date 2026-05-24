<x-filament-panels::page>
    <div class="mb-4">
        <h2 class="text-xl font-bold dark:text-white">Resumen de su Cuenta</h2>
        <p class="text-gray-600 dark:text-gray-300">
            Bienvenido, {{ auth()->user()->name }}. Aquí puede revisar el historial de facturas, deudas y pagos de sus locales comerciales asignados. Puede descargar el recibo PDF en cualquier momento.
        </p>
    </div>
    {{ $this->table }}
</x-filament-panels::page>
