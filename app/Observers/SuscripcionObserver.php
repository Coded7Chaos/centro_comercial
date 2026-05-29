<?php

namespace App\Observers;

use App\Models\Suscripciones;
use App\Models\InfraestructurasTiendas;
use App\Models\EstadoTienda;

class SuscripcionObserver
{
    /**
     * Sincroniza el estado de una tienda en base a las suscripciones activas el día de hoy.
     */
    public static function syncTienda(?int $tiendaId): void
    {
        if (!$tiendaId) {
            return;
        }

        $tienda = InfraestructurasTiendas::find($tiendaId);
        if (!$tienda) {
            return;
        }

        $today = now()->toDateString();

        // Buscar si existe un contrato activo para el día de hoy en esta tienda
        $suscripcionActiva = Suscripciones::where('infraestructuras_tienda_id', $tiendaId)
            ->where('fecha_inicio', '<=', $today)
            ->where('fecha_fin', '>=', $today)
            ->first();

        if ($suscripcionActiva) {
            $estadoAlquilada = EstadoTienda::where('estado', 'Alquilada')->first();
            if ($estadoAlquilada) {
                $tienda->update([
                    'cliente_id' => $suscripcionActiva->cliente_id,
                    'marca_id' => $suscripcionActiva->marca_id,
                    'id_estado' => $estadoAlquilada->id,
                ]);
            }
        } else {
            $estadoDisponible = EstadoTienda::where('estado', 'Disponible')->first();
            if ($estadoDisponible) {
                $tienda->update([
                    'cliente_id' => null,
                    'marca_id' => null,
                    'id_estado' => $estadoDisponible->id,
                ]);
            }
        }
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
