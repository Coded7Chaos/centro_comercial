<?php

namespace App\Filament\Resources\Suscripciones\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuscripcionesTable
{
    public static function configure(
        Table $table
    ): Table {

        return $table

            ->columns([

                /*
    |------------------------------------------------------------------
    | CLIENTE
    |------------------------------------------------------------------
    */

                TextColumn::make('cliente')

                    ->label('Cliente')

                    ->getStateUsing(function ($record) {

                        return

                            $record->cliente?->id .
                            ' - ' .
                            trim(
                                $record->cliente?->nombres .
                                    ' ' .
                                    $record->cliente?->apellidos
                            );
                    })

                    ->limit(30)

                    ->searchable(),


                /*
    |------------------------------------------------------------------
    | TIENDA
    |------------------------------------------------------------------
    */

                TextColumn::make('infraestructuras_tienda_id')

                    ->label('Tienda')

                    ->getStateUsing(function ($record) {

                        $tienda =
                            $record->infraestructurasTienda;

                        if (!$tienda) {
                            return '-';
                        }

                        return

                            'Tienda #' .
                            $tienda->numero .
                            ' - ' .
                            ($tienda->nombre ?? 'Sin nombre');
                    })

                    ->limit(25)

                    ->searchable(),


                /*
    |------------------------------------------------------------------
    | TIPO
    |------------------------------------------------------------------
    */

                TextColumn::make('tipo')

                    ->badge()

                    ->color(function ($state) {

                        return match ($state) {

                            'semanal' => 'gray',

                            'mensual' => 'success',

                            'bimestral' => 'info',

                            'trimestral' => 'warning',

                            'semestral' => 'primary',

                            'anual' => 'danger',

                            'personalizado' => 'purple',

                            default => 'secondary',
                        };
                    })

                    ->sortable()

                    ->searchable(),


                /*
    |------------------------------------------------------------------
    | PRECIO
    |------------------------------------------------------------------
    */

                TextColumn::make('precio')

                    ->label('Precio')

                    ->formatStateUsing(
                        fn($state) =>

                        'Bs. ' .
                            number_format($state, 2, ',', '.')
                    )

                    ->sortable(),


                /*
    |------------------------------------------------------------------
    | FECHAS
    |------------------------------------------------------------------
    */

                TextColumn::make('fecha_inicio')

                    ->label('Fecha inicio')

                    ->date()

                    ->sortable(),

                TextColumn::make('fecha_fin')

                    ->label('Fecha fin')

                    ->date()

                    ->sortable(),


                /*
    |------------------------------------------------------------------
    | CREATED / UPDATED
    |------------------------------------------------------------------
    */

                TextColumn::make('created_at')

                    ->dateTime()

                    ->sortable()

                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('updated_at')

                    ->dateTime()

                    ->sortable()

                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            ->filters([
                //
            ])

            ->recordActions([

                EditAction::make(),

                Action::make('movimiento')

                    ->label('Movimiento')

                    ->icon('heroicon-o-chart-bar')

                    ->color('success')

                    ->url(
                        fn($record) =>

                        route(
                            'pdf.suscripcion.movimiento',
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
