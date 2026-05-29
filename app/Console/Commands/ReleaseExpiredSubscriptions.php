<?php

namespace App\Console\Commands;

use App\Models\InfraestructurasTiendas;
use App\Observers\SuscripcionObserver;
use Illuminate\Console\Command;

class ReleaseExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suscripciones:liberar-expiradas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza y libera de forma diaria el estado de ocupación de las tiendas según la vigencia de los contratos.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tiendas = InfraestructurasTiendas::all();
        $contador = 0;

        foreach ($tiendas as $tienda) {
            $estadoAnterior = $tienda->id_estado;
            
            // Invocar sincronización centralizada
            SuscripcionObserver::syncTienda($tienda->id);
            
            // Verificar si el estado cambió a "Disponible" (liberación)
            $tienda->refresh();
            if ($estadoAnterior !== $tienda->id_estado && $tienda->estado?->estado === 'Disponible') {
                $contador++;
            }
        }

        $this->info("Proceso completado. Tiendas liberadas hoy por expiración: {$contador}");

        return self::SUCCESS;
    }
}
