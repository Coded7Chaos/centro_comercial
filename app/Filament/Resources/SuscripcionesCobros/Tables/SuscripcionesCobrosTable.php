<?php

namespace App\Filament\Resources\SuscripcionesCobros\Tables;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

                    ->searchable(query: function ($query, string $search) {
                        return $query->whereRaw("unaccent(lower(concepto)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                    })

                    ->limit(40)

                    ->tooltip(
                        fn ($record) => $record->concepto
                    ),

                TextColumn::make('cliente')

                    ->label('Cliente')

                    ->state(fn ($record) => $record->suscripcion?->cliente?->nombre_completo ?? 'Sin cliente')

                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('suscripcion.cliente.user', function ($q) use ($search) {
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_materno)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
                        });
                    })

                    ->limit(32),

                /*
                |--------------------------------------------------------------------------
                | MONTO A PAGAR
                |--------------------------------------------------------------------------
                */

                TextColumn::make('monto')

                    ->label('Monto a pagar')

                    ->money('BOB')

                    ->sortable()

                    ->visible(fn ($livewire) => in_array($livewire->activeTab ?? 'mensuales', ['mensuales', 'parciales', 'todos'])),

                /*
                 |--------------------------------------------------------------------------
                 | FECHA DE VENCIMIENTO (FECHA PAGO TEÓRICA)
                 |--------------------------------------------------------------------------
                 */

                TextColumn::make('fecha_vencimiento')

                    ->label('Fecha pago')

                    ->date('d/m/Y')

                    ->sortable()

                    ->visible(fn ($livewire) => in_array($livewire->activeTab ?? 'mensuales', ['mensuales', 'parciales', 'todos'])),

                /*
                 |--------------------------------------------------------------------------
                 | MONTO DE DEUDA (MOROSOS)
                 |--------------------------------------------------------------------------
                 */

                TextColumn::make('monto_deuda')

                    ->label('Monto de deuda')

                    ->money('BOB')

                    ->state(function ($record) {
                        $pagado = $record->pagos()->sum('monto_pagado');

                        return max(0, $record->monto - $pagado);
                    })

                    ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === 'morosos'),

                /*
                 |--------------------------------------------------------------------------
                 | DÍAS SIN PAGAR (MOROSOS)
                 |--------------------------------------------------------------------------
                 */

                TextColumn::make('dias_sin_pagar')

                    ->label('Días sin pagar')

                    ->state(function ($record) {
                        if (! $record->fecha_vencimiento) {
                            return '---';
                        }
                        $venc = Carbon::parse($record->fecha_vencimiento)->startOfDay();
                        $hoy = now()->startOfDay();

                        return max(0, $venc->diffInDays($hoy, false));
                    })

                    ->badge()

                    ->color('danger')

                    ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === 'morosos'),

                /*
                 |--------------------------------------------------------------------------
                 | ESTADO (badge color-coded)
                 |--------------------------------------------------------------------------
                 */

                TextColumn::make('estado')

                    ->label('Estado')

                    ->badge()

                    ->color(fn (string $state): string => match ($state) {
                        'pagado'   => 'success',
                        'pendiente' => 'warning',
                        'parcial'  => 'info',
                        'vencido'  => 'danger',
                        'anulado'  => 'gray',
                        default    => 'gray',
                    })

                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pagado'   => 'Pagado',
                        'pendiente' => 'Pendiente',
                        'parcial'  => 'Pago parcial',
                        'vencido'  => 'Moroso',
                        'anulado'  => 'Anulado',
                        default    => ucfirst($state),
                    }),
            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make(),

                Action::make('pdf')

                    ->label('PDF')

                    ->icon('heroicon-o-document-text')

                    ->color('danger')

                    ->url(
                        fn ($record) => route('cobros.pdf', $record->id)
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
