<?php

namespace App\Filament\Resources\SuscripcionesPagos\Pages;

use App\Filament\Resources\SuscripcionesPagos\SuscripcionesPagosResource;
use App\Models\SuscripcionesCobros;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateSuscripcionesPagos extends CreateRecord
{
    protected static string $resource = SuscripcionesPagosResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        if (
            empty($data['suscripcion_cobro_id'])
        ) {

            Notification::make()

                ->title('No existen deudas pendientes')

                ->body(
                    'La tienda seleccionada no tiene pagos pendientes.'
                )

                ->danger()

                ->send();

            throw ValidationException::withMessages([

                'tienda_id' =>

                'La tienda no tiene pagos pendientes.',
            ]);
        }
    }

    protected function afterCreate(): void
    {
        $pago = $this->record;

        $cobro = SuscripcionesCobros::find(
            $pago->suscripcion_cobro_id
        );

        if (! $cobro) {
            return;
        }

        /*
    |--------------------------------------------------------------------------
    | TOTAL PAGADO ACUMULADO
    |--------------------------------------------------------------------------
    */

        $totalPagado = $cobro->pagos()->sum(
            'monto_pagado'
        );

        /*
    |--------------------------------------------------------------------------
    | SALDO PENDIENTE
    |--------------------------------------------------------------------------
    */

        $saldoPendiente =
            $cobro->monto - $totalPagado;

        /*
    |--------------------------------------------------------------------------
    | EVITAR NEGATIVOS
    |--------------------------------------------------------------------------
    */

        if ($saldoPendiente < 0) {
            $saldoPendiente = 0;
        }

        /*
    |--------------------------------------------------------------------------
    | ESTADO
    |--------------------------------------------------------------------------
    */

        if ($saldoPendiente == 0) {

            $estado = 'pagado';
        } elseif ($totalPagado > 0) {

            $estado = 'parcial';
        } else {

            $estado = 'pendiente';
        }

        /*
    /*
|--------------------------------------------------------------------------
| ACTUALIZAR EL PAGO RECIÉN CREADO
|--------------------------------------------------------------------------
*/

        $pago->update([

            /*
    | Nuevo saldo restante después de este pago
    */
            'pago_pendiente' => $saldoPendiente,
            'estado_snapshot' => $estado,
        ]);

        /*
|--------------------------------------------------------------------------
| ACTUALIZAR COBRO
|--------------------------------------------------------------------------
*/

        $cobro->update([

            'saldo_pendiente' =>
            $saldoPendiente,

            'estado' =>
            $estado,

            'estado_snapshot' => $estado,
        ]);
    }
}
