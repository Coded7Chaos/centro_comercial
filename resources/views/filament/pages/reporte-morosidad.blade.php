<x-filament-panels::page>
    <div class="mb-4">
        <p class="text-gray-600 dark:text-gray-300">
            Este reporte lista los cobros cuya fecha de vencimiento ya expiró y aún no fueron cancelados totalmente. Los estados "vencido" se actualizan automáticamente cada noche.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl border border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-900/20 p-4">
            <div class="text-xs font-medium uppercase tracking-wider text-red-700 dark:text-red-300">Deuda vencida</div>
            <div class="text-2xl font-bold text-red-700 dark:text-red-200 mt-1">
                Bs. {{ number_format($deudaVencida, 2) }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900 p-4">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Clientes morosos</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ $totalClientes }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900 p-4">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cobros en mora</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ $totalCobros }}
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
