<?php

namespace App\Console\Commands;

use App\Services\ExpiredSubscriptionsService;
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
    public function handle(ExpiredSubscriptionsService $service): int
    {
        $contador = $service->process();

        $this->info("Proceso completado. Tiendas liberadas hoy por expiración: {$contador}");

        return self::SUCCESS;
    }
}
