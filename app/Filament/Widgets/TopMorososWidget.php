<?php

namespace App\Filament\Widgets;

use App\Models\Clientes;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopMorososWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('View:TopMorososWidget') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Top 5 inquilinos con mayor deuda vencida';
    }

    public function table(Table $table): Table
    {
        // Deuda vencida por cliente = SUM(monto del cobro - SUM(monto_pagado)) por cada cobro
        // vencido y no totalmente pagado. PG no permite usar el alias del SELECT en HAVING/WHERE,
        // por eso repetimos la subquery en whereRaw/orderByRaw.
        $deudaSql = "(
            SELECT COALESCE(SUM(
                suscripciones_cobros.monto -
                COALESCE(
                    (SELECT SUM(sp.monto_pagado)
                     FROM suscripciones_pagos sp
                     WHERE sp.suscripcion_cobro_id = suscripciones_cobros.id),
                    0
                )
            ), 0)
            FROM suscripciones_cobros
            INNER JOIN suscripciones
                ON suscripciones.id = suscripciones_cobros.suscripcion_id
            WHERE suscripciones.cliente_id = clientes.id
              AND suscripciones_cobros.estado IN ('vencido','parcial','pendiente')
              AND suscripciones_cobros.fecha_vencimiento < CURRENT_DATE
        )";

        return $table
            ->query(
                Clientes::query()
                    ->select('clientes.*')
                    ->selectRaw("{$deudaSql} AS deuda_vencida")
                    ->whereRaw("{$deudaSql} > 0")
                    ->orderByRaw("{$deudaSql} DESC")
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('nombre_completo')
                    ->label('Inquilino')
                    ->state(fn (Clientes $r) => trim(($r->user->nombres ?? '') . ' ' . ($r->user->apellido_paterno ?? ''))),
                TextColumn::make('ci')->label('CI'),
                TextColumn::make('numero_celular')->label('Celular')->placeholder('—'),
                TextColumn::make('deuda_vencida')
                    ->label('Deuda vencida')
                    ->money('BOB')
                    ->weight('bold')
                    ->color('danger'),
            ])
            ->paginated(false);
    }
}
