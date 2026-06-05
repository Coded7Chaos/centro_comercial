<?php

namespace App\Filament\Resources\Suscripciones\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('cliente.user', function ($q) use ($search) {
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_materno)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
                        });
                    }),


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

                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('infraestructurasTienda', function ($q) use ($search) {
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(numero)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
                        });
                    }),


                /*
    |------------------------------------------------------------------
    | TIPO
    |------------------------------------------------------------------
    */

                TextColumn::make('tipo')
                    ->label('Duración del contrato')
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

                    ->searchable(query: function ($query, string $search) {
                        return $query->whereRaw("unaccent(lower(tipo)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                    }),


                /*
    |------------------------------------------------------------------
    | PAGO MENSUAL
    |------------------------------------------------------------------
    */

                TextColumn::make('pago_mensual')
                    ->label('Pago Mensual')
                    ->getStateUsing(function ($record) {
                        $meses = max(1, (int) round(
                            \Carbon\Carbon::parse($record->fecha_inicio)
                                ->diffInMonths(\Carbon\Carbon::parse($record->fecha_fin)->addDay())
                        ));
                        return $record->precio / $meses;
                    })
                    ->formatStateUsing(fn ($state) => 'Bs. ' . number_format($state, 2, ',', '.'))
                    ->sortable(false),

                /*
    |------------------------------------------------------------------
    | PRECIO TOTAL (pago mensual × meses + garantía)
    |------------------------------------------------------------------
    */

                TextColumn::make('precio')
                    ->label('Precio Total')
                    ->getStateUsing(function ($record) {
                        $meses = max(1, (int) round(
                            \Carbon\Carbon::parse($record->fecha_inicio)
                                ->diffInMonths(\Carbon\Carbon::parse($record->fecha_fin)->addDay())
                        ));
                        $pagoMensual = $record->precio / $meses;
                        return $record->precio + $pagoMensual;
                    })
                    ->formatStateUsing(fn ($state) => 'Bs. ' . number_format($state, 2, ',', '.'))
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

                ViewAction::make(),

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
                    ->url(fn ($record) => route('admin.suscripciones.renovar-custom', $record->id)),
            ])

            ->toolbarActions([]);
    }
}
