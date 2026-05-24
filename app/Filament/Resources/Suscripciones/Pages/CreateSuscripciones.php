<?php

namespace App\Filament\Resources\Suscripciones\Pages;

use App\Filament\Resources\Suscripciones\SuscripcionesResource;
use App\Models\Suscripciones;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Models\SuscripcionesCobros;
use App\Models\InfraestructurasTiendas;
use App\Models\EstadoTienda;

class CreateSuscripciones extends CreateRecord
{
    protected static string $resource = SuscripcionesResource::class;

    /*
    |--------------------------------------------------------------------------
    | VALIDACIÓN ANTI DUPLICADOS
    |--------------------------------------------------------------------------
    */

    protected function beforeCreate(): void
    {
        $data = $this->data;

        $existe = Suscripciones::where(
            'infraestructuras_tienda_id',
            $data['infraestructuras_tienda_id']
        )
            ->where(function ($query) use ($data) {
                $query
                    ->whereBetween(
                        'fecha_inicio',
                        [
                            $data['fecha_inicio'],
                            $data['fecha_fin']
                        ]
                    )
                    ->orWhereBetween(
                        'fecha_fin',
                        [
                            $data['fecha_inicio'],
                            $data['fecha_fin']
                        ]
                    )
                    ->orWhere(function ($sub) use ($data) {
                        $sub
                            ->where(
                                'fecha_inicio',
                                '<=',
                                $data['fecha_inicio']
                            )
                            ->where(
                                'fecha_fin',
                                '>=',
                                $data['fecha_fin']
                            );
                    });
            })
            ->exists();

        if ($existe) {
            Notification::make()
                ->title(
                    'Ya existe una suscripción activa para esta tienda en ese periodo.'
                )
                ->danger()
                ->send();

            $this->halt();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR TIENDA AUTOMÁTICAMENTE
    |--------------------------------------------------------------------------
    */

    protected function afterCreate(): void
    {
        $tienda = InfraestructurasTiendas::find($this->record->infraestructuras_tienda_id);
        $estadoAlquilada = EstadoTienda::where('estado', 'Alquilada')->first();

        if ($tienda && $estadoAlquilada) {
            $tienda->update([
                'cliente_id' => $this->record->cliente_id,
                'id_estado' => $estadoAlquilada->id,
            ]);
        }
    }
}
