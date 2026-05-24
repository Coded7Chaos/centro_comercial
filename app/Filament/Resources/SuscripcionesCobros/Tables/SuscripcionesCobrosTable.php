<?php

namespace App\Filament\Resources\SuscripcionesCobros\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuscripcionesCobrosTable
{
    public static function configure(
        Table $table
    ): Table {

        return $table
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | CONCEPTO
                |--------------------------------------------------------------------------
                */

                TextColumn::make('concepto')

                    ->label('Concepto del cobro')

                    ->searchable()

                    ->limit(40)

                    ->tooltip(
                        fn($record) =>
                        $record->concepto
                    ),

                /*
                |--------------------------------------------------------------------------
                | MONTO
                |--------------------------------------------------------------------------
                */

                TextColumn::make('monto')

                    ->label('Monto a pagar')

                    ->money('BOB')

                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | FECHA INICIO
                |--------------------------------------------------------------------------
                */

                TextColumn::make('fecha_inicio')

                    ->label('Fecha inicio')

                    ->date('d/m/Y')

                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | FECHA PAGO
                |--------------------------------------------------------------------------
                */

                TextColumn::make('fecha_pago')

                    ->label('Fecha pago')

                    ->date('d/m/Y')

                    ->placeholder('Sin registrar')

                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | ESTADO
                |--------------------------------------------------------------------------
                */

                BadgeColumn::make('estado')

                    ->colors([

                        'warning' => 'pendiente',

                        'success' => 'pagado',

                        'danger' => 'vencido',

                        'gray' => 'anulado',
                    ])

                    ->formatStateUsing(fn($state) => match ($state) {

                        'pendiente' => 'Pendiente',

                        'pagado' => 'Pagado',

                        'vencido' => 'Vencido',

                        'anulado' => 'Anulado',

                        default => ucfirst($state),
                    }),
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
                        route('cobros.pdf', $record->id)
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