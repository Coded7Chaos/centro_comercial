<?php

namespace App\Filament\Resources\SuscripcionesPagos\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_materno)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
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
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('cobro.suscripcion.infraestructurasTienda', function ($q) use ($search) {
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(numero)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
                        });
                    })
                    ->sortable(),

                /*
                |------------------------------------------------------------------
                | CONCEPTO (heredado del cobro asociado)
                |------------------------------------------------------------------
                */

                TextColumn::make('cobro.concepto')
                    ->label('Concepto')
                    ->wrap()
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('cobro', function ($q) use ($search) {
                            $q->whereRaw("unaccent(lower(concepto)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                        });
                    })
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->cobro?->concepto),

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

            ->filters([])

            ->recordActions([

                ViewAction::make(),

                Action::make('pdf')

                    ->label('PDF')

                    ->icon('heroicon-o-document-text')

                    ->color('danger')

                    ->visible(fn ($record) => $record->estado_verificacion === 'verificado')

                    ->url(

                        fn($record) =>

                        route(

                            'pdf.pago',

                            $record->id

                        )

                    )

                    ->openUrlInNewTab(),

                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->estado_verificacion === 'pendiente')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['estado_verificacion' => 'verificado']);
                        $cobro = $record->cobro;
                        
                        // Recalculate status of the cobro (it will become pagado since amount is fully paid)
                        $cobro->update([
                            'saldo_pendiente' => 0,
                            'estado' => 'pagado',
                            'estado_snapshot' => 'pagado',
                        ]);

                        // Send success notification to client
                        \App\Models\ClientNotification::create([
                            'cliente_id' => $cobro->suscripcion->cliente_id,
                            'tipo' => 'success',
                            'titulo' => 'Pago Aprobado',
                            'mensaje' => 'Su pago para el cobro "' . $cobro->concepto . '" por Bs. ' . number_format($record->monto_pagado, 2) . ' ha sido aprobado.',
                        ]);
                    }),

                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->estado_verificacion === 'pendiente')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('motivo_rechazo')
                            ->label('Razón del Rechazo')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'estado_verificacion' => 'rechazado',
                            'motivo_rechazo' => $data['motivo_rechazo'],
                        ]);
                        $cobro = $record->cobro;
                        
                        // Revert cobro status to original (either vencido if date is past, or pendiente)
                        $originalEstado = now()->toDateString() > $cobro->fecha_vencimiento ? 'vencido' : 'pendiente';
                        $cobro->update([
                            'estado' => $originalEstado,
                            'estado_snapshot' => $originalEstado,
                        ]);

                        // Send rejection notification to client
                        \App\Models\ClientNotification::create([
                            'cliente_id' => $cobro->suscripcion->cliente_id,
                            'tipo' => 'danger',
                            'titulo' => 'Pago Rechazado',
                            'mensaje' => 'Su pago para el cobro "' . $cobro->concepto . '" por Bs. ' . number_format($record->monto_pagado, 2) . ' ha sido rechazado. Razón: ' . $data['motivo_rechazo'],
                        ]);
                    }),
            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
