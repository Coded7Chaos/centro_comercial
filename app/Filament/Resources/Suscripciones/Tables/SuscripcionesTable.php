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
                        return $record->cliente?->id . ' - ' . ($record->cliente?->nombre_completo ?? 'N/A');
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

                Action::make('contrato_pdf')
                    ->label('Contrato PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn($record) => route('pdf.contrato', $record->id))
                    ->openUrlInNewTab(),

                Action::make('renovar')
                    ->label('Renovar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('fecha_inicio')
                            ->label('Fecha Inicio')
                            ->default(fn ($record) => $record->fecha_fin)
                            ->required(),
                        \Filament\Forms\Components\Select::make('tipo')
                            ->label('Tipo de Suscripción')
                            ->options([
                                'anual' => 'Anual',
                                'semestral' => 'Semestral',
                                'trimestral' => 'Trimestral',
                                'bimestral' => 'Bimestral',
                                'mensual' => 'Mensual',
                                'semanal' => 'Semanal',
                                'personalizado' => 'Personalizado',
                            ])
                            ->default(fn ($record) => $record->tipo)
                            ->required(),
                        \Filament\Forms\Components\DatePicker::make('fecha_fin')
                            ->label('Fecha Fin')
                            ->default(fn ($record) => \Carbon\Carbon::parse($record->fecha_fin)->addMonth())
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('precio')
                            ->label('Precio')
                            ->numeric()
                            ->prefix('Bs.')
                            ->default(fn ($record) => $record->precio)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        \App\Models\Suscripciones::create([
                            'cliente_id' => $record->cliente_id,
                            'marca_id' => $record->marca_id,
                            'infraestructuras_tienda_id' => $record->infraestructuras_tienda_id,
                            'infraestructuras_piso_id' => $record->infraestructuras_piso_id,
                            'tipo' => $data['tipo'],
                            'precio' => $data['precio'],
                            'fecha_inicio' => $data['fecha_inicio'],
                            'fecha_fin' => $data['fecha_fin'],
                            'tamano' => $record->tamano,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Contrato Renovado')
                            ->body('Se ha creado un nuevo contrato de arrendamiento exitosamente.')
                            ->success()
                            ->send();
                    }),
            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make(),

                ]),
            ]);
    }
}
