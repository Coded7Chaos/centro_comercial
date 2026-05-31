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

        $saldoPendiente = $cobro->monto - $totalPagado;

        /*
        |--------------------------------------------------------------------------
        | EVITAR NEGATIVOS
        |--------------------------------------------------------------------------
        */

        if ($saldoPendiente < 0) {
            $saldoPendiente = 0;
        }

        $data = $this->form->getState();

        if ($saldoPendiente > 0) {
            // There is a pending balance. We split it into a new cobro!
            $opcion = $data['cobro_pendiente_opcion'] ?? 'siguiente_mes';
            $fechaVencimientoCobro = null;
            if ($opcion === 'fecha_intermedia' && !empty($data['fecha_cobro_pendiente'])) {
                $fechaVencimientoCobro = $data['fecha_cobro_pendiente'];
            } else {
                $fechaVencimientoCobro = \Carbon\Carbon::parse($cobro->fecha_vencimiento)->addMonth()->toDateString();
            }

            // Create new SuscripcionesCobros
            SuscripcionesCobros::create([
                'suscripcion_id' => $cobro->suscripcion_id,
                'concepto' => 'Saldo pendiente de: ' . $cobro->concepto,
                'monto' => $saldoPendiente,
                'fecha_inicio' => $pago->fecha_pago ?? now()->toDateString(),
                'fecha_vencimiento' => $fechaVencimientoCobro,
                'estado' => 'pendiente',
                'observaciones' => 'Cobro generado de saldo pendiente del pago #' . $pago->id,
                'es_parcial' => true,
            ]);

            // Adjust original cobro
            $cobro->update([
                'monto' => $totalPagado,
                'saldo_pendiente' => 0,
                'estado' => 'pagado',
                'estado_snapshot' => 'pagado',
            ]);

            // Adjust payment snapshot
            $pago->update([
                'pago_pendiente' => 0,
                'estado_snapshot' => 'pagado',
            ]);
        } else {
            // Fully paid
            $pago->update([
                'pago_pendiente' => 0,
                'estado_snapshot' => 'pagado',
            ]);

            $cobro->update([
                'saldo_pendiente' => 0,
                'estado' => 'pagado',
                'estado_snapshot' => 'pagado',
            ]);
        }
    }
}
