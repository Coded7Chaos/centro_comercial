<?php

namespace App\Filament\Resources\SuscripcionesPagos\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuscripcionesPagosTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([

                /*
                |------------------------------------------------------------------
                | CLIENTE
                |------------------------------------------------------------------
                */

                TextColumn::make('cliente')

                    ->label('Cliente')

                    ->state(function ($record) {

                        $cliente =
                            $record
                            ?->cobro
                            ?->suscripcion
                            ?->cliente;

                        return ($cliente?->nombres ?? '')

                            . ' '

                            .

                            ($cliente?->apellidos ?? '');
                    })

                    ->limit(25)

                    ->tooltip(function ($record) {

                        $cliente =
                            $record
                            ?->cobro
                            ?->suscripcion
                            ?->cliente;

                        return ($cliente?->nombres ?? '')

                            . ' '

                            .

                            ($cliente?->apellidos ?? '');
                    })

                    ->searchable()

                    ->sortable()

                    ->weight('medium'),

                /*
                |------------------------------------------------------------------
                | TIENDA
                |------------------------------------------------------------------
                */

                TextColumn::make('tienda')

                    ->label('Tienda')

                    ->state(function ($record) {

                        return
                            $record
                            ?->cobro
                            ?->suscripcion
                            ?->infraestructurasTienda
                            ?->nombre;
                    })

                    ->limit(20)

                    ->tooltip(function ($record) {

                        $tienda =
                            $record
                            ?->cobro
                            ?->suscripcion
                            ?->infraestructurasTienda;

                        $piso =
                            $tienda?->piso;

                        $infra =
                            $piso?->infraestructura;

                        return ($tienda?->nombre ?? 'Sin nombre')

                            . "\n"

                            .

                            ($infra?->nombre ?? 'Sin infraestructura')

                            . ' - Piso '

                            .

                            ($piso?->nombre ?? '---');
                    })

                    ->searchable()

                    ->sortable(),

                /*
                |------------------------------------------------------------------
                | PAGO
                |------------------------------------------------------------------
                */

                TextColumn::make('monto_pagado')

                    ->label('Pago')

                    ->money('BOB')

                    ->weight('bold')

                    ->color('success')

                    ->description(
                        fn($record) =>

                        ucfirst($record->metodo_pago)
                    )

                    ->sortable(),

                /*
                |------------------------------------------------------------------
                | PENDIENTE
                |------------------------------------------------------------------
                */

                TextColumn::make('pago_pendiente')

                    ->label('Pendiente')

                    ->money('BOB')

                    ->badge()

                    ->color(
                        fn($state) =>

                        $state <= 0

                            ? 'success'

                            : 'danger'
                    ),

                /*
                |------------------------------------------------------------------
                | ESTADO
                |------------------------------------------------------------------
                */

                TextColumn::make('estado_snapshot')

                    ->label('Estado del pago')

                    ->badge()

                    ->color(function ($state) {

                        return match ($state) {

                            'pagado' => 'success',

                            'parcial' => 'warning',

                            'pendiente' => 'gray',

                            'vencido' => 'danger',

                            default => 'gray',
                        };
                    }),

                /*
                |------------------------------------------------------------------
                | FECHA
                |------------------------------------------------------------------
                */

                TextColumn::make('fecha_pago')

                    ->label('Fecha')

                    ->date('d/m/Y')

                    ->sinceTooltip()

                    ->sortable(),
            ])

            ->filters([
                //
            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make(),

                Action::make('pdf')

                    ->label('PDF')

                    ->icon('heroicon-o-document-text')

                    ->color('danger')

                    ->url(
                        fn($record) =>

                        route(
                            'pdf.pago',
                            $record->id
                        )
                    )

                    ->openUrlInNewTab(),
            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
