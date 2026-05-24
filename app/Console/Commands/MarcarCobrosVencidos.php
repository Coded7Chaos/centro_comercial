<?php

namespace App\Console\Commands;

use App\Models\SuscripcionesCobros;
use Illuminate\Console\Command;

class MarcarCobrosVencidos extends Command
{
    protected $signature = 'cobros:marcar-vencidos';

    protected $description = 'Marca como "vencido" los cobros cuya fecha_vencimiento ya pasó y aún están en estado pendiente o parcial.';

    public function handle(): int
    {
        $afectados = SuscripcionesCobros::query()
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->update(['estado' => 'vencido']);

        $this->info("Cobros marcados como vencidos: {$afectados}");

        return self::SUCCESS;
    }
}
