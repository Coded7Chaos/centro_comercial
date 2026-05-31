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

                TextColumn::make('cobro.suscripcion.cliente.user.nombres')
                    ->label('Cliente')
                    ->formatStateUsing(function ($record) {
                        $user = $record->cobro?->suscripcion?->cliente?->user;
                        if (!$user) return '---';
                        return "{$user->nombres} {$user->apellido_paterno} {$user->apellido_materno}";
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('cobro.suscripcion.cliente.user', function ($q) use ($search) {
                            $q->where('nombres', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->weight('medium'),

                /*
                |------------------------------------------------------------------
                | TIENDA
                |------------------------------------------------------------------
                */

                TextColumn::make('cobro.suscripcion.infraestructurasTienda.nombre')
                    ->label('Tienda')
                    ->limit(20)
                    ->tooltip(function ($record) {
                        $tienda = $record->cobro?->suscripcion?->infraestructurasTienda;
                        if (!$tienda) return 'Sin tienda';
                        
                        $piso = $tienda->piso;
                        $infra = $piso?->infraestructura;

                        return ($tienda->nombre ?? 'Sin nombre')
                            . "\n"
                            . ($infra?->nombre ?? 'Sin infraestructura')
                            . ' - Piso '
                            . ($piso?->nombre ?? '---');
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

                    ->badge(fn($state) => $state > 0)

                    ->color(
                        fn($state) =>

                        $state <= 0

                            ? null

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

                    ->formatStateUsing(function ($state) {
                        return $state === 'pagado' ? 'completo' : $state;
                    })

                    ->color(function ($state) {

                        return match ($state) {

                            'pagado', 'completo' => 'success',

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

                TextColumn::make('dias_diferencia')
                    ->label('Días de Diferencia')
                    ->state(function ($record) {
                        $vencimiento = $record->cobro?->fecha_vencimiento;
                        $pago = $record->fecha_pago;
                        if (!$vencimiento || !$pago) return '---';

                        $vDate = \Carbon\Carbon::parse($vencimiento)->startOfDay();
                        $pDate = \Carbon\Carbon::parse($pago)->startOfDay();
                        return (int)$vDate->diffInDays($pDate, false);
                    })
                    ->badge(fn($state) => $state !== '---' && $state !== 0)
                    ->color(function ($state) {
                        if ($state === '---' || $state === 0) return null;
                        return $state > 0 ? 'danger' : 'success';
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state === '---') return '---';
                        $val = (int)$state;
                        if ($val > 0) {
                            return "+{$val} días (Tardío)";
                        } elseif ($val < 0) {
                            return "{$val} días (Anticipado)";
                        } else {
                            return "0 días";
                        }
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
