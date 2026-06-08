<?php

namespace App\Observers;

use App\Models\Suscripciones;
use App\Services\ExpiredSubscriptionsService;

class SuscripcionObserver
{
    /**
     * Sincroniza el estado de una tienda en base a las suscripciones activas el día de hoy.
     */
    public static function syncTienda(?int $tiendaId): void
    {
        if (! $tiendaId) {
            return;
        }

        app(ExpiredSubscriptionsService::class)->syncStoreForToday($tiendaId);
    }

    /**
     * Handle the Suscripciones "created" event.
     */
    public function created(Suscripciones $suscripcion): void
    {
        self::syncTienda($suscripcion->infraestructuras_tienda_id);
    }

    /**
     * Handle the Suscripciones "updated" event.
     */
    public function updated(Suscripciones $suscripcion): void
    {
        if ($suscripcion->isDirty('infraestructuras_tienda_id')) {
            $oldTiendaId = $suscripcion->getOriginal('infraestructuras_tienda_id');
            self::syncTienda($oldTiendaId);
        }

        self::syncTienda($suscripcion->infraestructuras_tienda_id);
    }

    /**
     * Handle the Suscripciones "deleted" event.
     */
    public function deleted(Suscripciones $suscripcion): void
    {
        self::syncTienda($suscripcion->infraestructuras_tienda_id);
    }
}
