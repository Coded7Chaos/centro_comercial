<?php

namespace App\Filament\Resources\SuscripcionesCobros\Schemas;

use App\Models\Suscripciones;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;

class SuscripcionesCobrosForm
{
    public static function configure(
        Schema $schema
    ): Schema {

        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | SUSCRIPCIÓN
                |--------------------------------------------------------------------------
                */

                Select::make('suscripcion_id')

                    ->label('Suscripción')

                    ->options(

                        Suscripciones::with([
                            'cliente',
                            'infraestructurasTienda'
                        ])

                            ->get()

                            ->mapWithKeys(function ($suscripcion) {

                                $cliente =
                                    $suscripcion->cliente;

                                $tienda =
                                    $suscripcion->infraestructurasTienda;

                                return [

                                    $suscripcion->id =>

                                    'Tienda #'

                                        .

                                        ($tienda?->numero ?? '---')

                                        .

                                        ' - '

                                        .

                                        ($tienda?->nombre ?? 'Sin nombre')

                                        .

                                        ' - '

                                        .

                                        ucfirst($suscripcion->tipo)

                                        .

                                        ' - '

                                        .

                                        ($cliente?->nombres ?? '')

                                        .

                                        ' '

                                        .

                                        ($cliente?->apellidos ?? '')
                                ];
                            })

                    )

                    ->searchable()

                    ->preload()

                    ->live()

                    ->afterStateUpdated(

                        function (
                            Get $get,
                            Set $set,
                            $state
                        ) {

                            $suscripcion =
                                Suscripciones::with(
                                    'infraestructurasTienda'
                                )->find($state);

                            if (!$suscripcion) {
                                return;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | TIENDA
                            |--------------------------------------------------------------------------
                            */

                            $tienda =
                                $suscripcion->infraestructurasTienda;

                            /*
                            |--------------------------------------------------------------------------
                            | CONCEPTO
                            |--------------------------------------------------------------------------
                            */

                            $concepto =

                                'Cobro '

                                .

                                ucfirst($suscripcion->tipo)

                                .

                                ' - '

                                .

                                ($tienda?->nombre ?? 'Sin nombre');

                            $set(
                                'concepto',
                                $concepto
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | MONTO
                            |--------------------------------------------------------------------------
                            */

                            $set(
                                'monto',
                                $suscripcion->precio
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | FECHA INICIO
                            |--------------------------------------------------------------------------
                            */

                            $set(
                                'fecha_inicio',
                                $suscripcion->fecha_inicio
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | FECHA VENCIMIENTO
                            |--------------------------------------------------------------------------
                            */

                            $set(
                                'fecha_vencimiento',
                                $suscripcion->fecha_fin
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | FECHA PAGO
                            |--------------------------------------------------------------------------
                            */

                            $set(
                                'fecha_pago',
                                now()->format('Y-m-d')
                            );
                        }

                    )

                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | CONCEPTO
                |--------------------------------------------------------------------------
                */

                TextInput::make('concepto')

                    ->label('Concepto del cobro')

                    ->readOnly()

                    ->required()

                    ->maxLength(255),

                /*
                |--------------------------------------------------------------------------
                | MONTO
                |--------------------------------------------------------------------------
                */

                TextInput::make('monto')

                    ->label('Monto a pagar')

                    ->numeric()

                    ->prefix('Bs.')

                    ->readOnly()

                    ->required()

                    ->minValue(0),

                TextInput::make('monto_pagado')

                    ->label('Monto pagado')

                    ->prefix('Bs.')

                    ->formatStateUsing(function ($record) {

                        return $record?->pagos()
                            ->sum('monto_pagado') ?? 0;
                    })

                    ->disabled()

                    ->dehydrated(false),

                TextInput::make('saldo_pendiente')

                    ->label('Saldo pendiente')

                    ->prefix('Bs.')

                    ->formatStateUsing(function ($record) {

                        if (! $record) {
                            return 0;
                        }

                        $pagado =
                            $record->pagos()
                            ->sum('monto_pagado');

                        return $record->monto - $pagado;
                    })

                    ->disabled()

                    ->dehydrated(false),

                /*
|--------------------------------------------------------------------------
| HISTORIAL DE PAGOS
|--------------------------------------------------------------------------
*/

                Repeater::make('pagos')

                    ->label('Historial de pagos realizados')

                    ->relationship()

                    ->schema([

                        TextInput::make('monto_pagado')
                            ->label('Monto pagado')
                            ->prefix('Bs.')
                            ->disabled(),

                        TextInput::make('pago_pendiente')
                            ->label('Saldo restante')
                            ->prefix('Bs.')
                            ->disabled(),

                        DatePicker::make('fecha_pago')
                            ->label('Fecha de pago')
                            ->disabled(),

                        TextInput::make('metodo_pago')
                            ->label('Método de pago')
                            ->disabled(),

                        TextInput::make('created_at')
                            ->label('Hora del registro')
                            ->formatStateUsing(
                                fn($state) =>
                                $state
                                    ? \Carbon\Carbon::parse($state)->format('H:i:s')
                                    : null
                            )
                            ->disabled(),

                    ])

                    ->columns(2)

                    ->addable(false)

                    ->deletable(false)

                    ->reorderable(false)

                    ->collapsed(false)

                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | FECHA INICIO
                |--------------------------------------------------------------------------
                */

                DatePicker::make('fecha_inicio')

                    ->label('Fecha inicio')

                    ->disabled()

                    ->dehydrated()

                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | FECHA VENCIMIENTO
                |--------------------------------------------------------------------------
                */

                DatePicker::make('fecha_vencimiento')

                    ->label('Fecha de vencimiento')

                    ->disabled()

                    ->dehydrated()

                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | ESTADO
                |--------------------------------------------------------------------------
                */

                TextInput::make('estado')

                    ->label('Estado')

                    ->disabled()

                    ->dehydrated(),

                /*
                |--------------------------------------------------------------------------
                | OBSERVACIONES
                |--------------------------------------------------------------------------
                */

                Textarea::make('observaciones')

                    ->label('Observaciones')

                    ->placeholder(
                        'Escriba observaciones relevantes...'
                    )

                    ->rules([
                        'nullable',
                        'regex:/[A-Za-zÁÉÍÓÚáéíóúÑñ]/'
                    ])

                    ->validationMessages([

                        'regex' =>
                        'Las observaciones deben contener al menos una letra.'
                    ])

                    ->columnSpanFull(),
            ]);
    }
}
