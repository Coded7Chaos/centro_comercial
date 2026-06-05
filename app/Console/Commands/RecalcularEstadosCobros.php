<?php

namespace App\Console\Commands;

use App\Models\SuscripcionesCobros;
use Illuminate\Console\Command;

class RecalcularEstadosCobros extends Command
{
    protected $signature = 'cobros:recalcular-estados';

    protected $description = 'Recalcula el estado de todos los cobros según sus pagos registrados.';

    public function handle(): int
    {
        $cobros = SuscripcionesCobros::with('pagos')->get();

        $actualizados = 0;

        foreach ($cobros as $cobro) {
            $estadoAnterior = $cobro->estado;
            $cobro->recalcularEstado();
            $cobro->refresh();

            if ($cobro->estado !== $estadoAnterior) {
                $actualizados++;
            }
        }

        $this->info("Cobros revisados: {$cobros->count()}. Estados corregidos: {$actualizados}.");

        return self::SUCCESS;
    }
}
