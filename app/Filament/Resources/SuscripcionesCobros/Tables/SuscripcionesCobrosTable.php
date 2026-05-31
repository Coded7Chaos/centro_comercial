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
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;

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
                | MONTO A PAGAR
                |--------------------------------------------------------------------------
                */

                TextColumn::make('monto')

                    ->label('Monto a pagar')

                    ->money('BOB')

                    ->sortable()

                    ->visible(fn ($livewire) => in_array($livewire->activeTab ?? 'mensuales', ['mensuales', 'parciales'])),

                /*
                |--------------------------------------------------------------------------
                | FECHA DE VENCIMIENTO (FECHA PAGO TEÓRICA)
                |--------------------------------------------------------------------------
                */

                TextColumn::make('fecha_vencimiento')

                    ->label('Fecha pago')

                    ->date('d/m/Y')

                    ->sortable()

                    ->visible(fn ($livewire) => in_array($livewire->activeTab ?? 'mensuales', ['mensuales', 'parciales'])),

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
                        if (!$record->fecha_vencimiento) return '---';
                        $venc = \Carbon\Carbon::parse($record->fecha_vencimiento)->startOfDay();
                        $hoy = now()->startOfDay();
                        return max(0, $venc->diffInDays($hoy, false));
                    })

                    ->badge()

                    ->color('danger')

                    ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === 'morosos'),
            ])

            ->filters([
                Filter::make('fecha_filtro')
                    ->form([
                        Select::make('anio')
                            ->label('Año')
                            ->options(function () {
                                $currentYear = now()->year;
                                $years = [];
                                for ($i = 0; $i <= 5; $i++) {
                                    $years[$currentYear + $i] = $currentYear + $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->reactive(),
                        Select::make('mes')
                            ->label('Mes')
                            ->options(function (callable $get) {
                                $selectedYear = $get('anio') ?? now()->year;
                                $currentYear = now()->year;
                                $currentMonth = now()->month;
                                
                                $months = [
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                ];
                                
                                if ((int)$selectedYear === (int)$currentYear) {
                                    return array_filter($months, function ($key) use ($currentMonth) {
                                        return $key >= $currentMonth;
                                    }, ARRAY_FILTER_USE_KEY);
                                }
                                
                                return $months;
                            })
                            ->default(now()->month),
                    ])
                    ->query(function (Builder $query, array $data, $livewire): Builder {
                        $activeTab = $livewire->activeTab ?? 'mensuales';
                        if ($activeTab !== 'mensuales') {
                            return $query;
                        }

                        $mes = $data['mes'] ?? now()->month;
                        $anio = $data['anio'] ?? now()->year;

                        return $query
                            ->whereMonth('fecha_vencimiento', $mes)
                            ->whereYear('fecha_vencimiento', $anio);
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (!empty($data['mes']) && !empty($data['anio'])) {
                            $months = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                            ];
                            $mesNom = $months[(int)$data['mes']] ?? '';
                            $indicators[] = "Período: {$mesNom} {$data['anio']}";
                        }
                        return $indicators;
                    })
            ])
            ->filtersLayout(FiltersLayout::AboveContent)

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