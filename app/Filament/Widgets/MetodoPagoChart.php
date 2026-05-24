<?php

namespace App\Filament\Widgets;

use App\Models\SuscripcionesPagos;
use Filament\Widgets\ChartWidget;

class MetodoPagoChart extends ChartWidget
{
    protected ?string $heading = 'Ingresos por método de pago';
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:MetodoPagoChart') ?? false;
    }

    protected function getData(): array
    {
        $rows = SuscripcionesPagos::query()
            ->selectRaw('metodo_pago, SUM(monto_pagado) as total')
            ->whereNotNull('metodo_pago')
            ->groupBy('metodo_pago')
            ->get();

        $colors = [
            'efectivo'      => '#16a34a',
            'transferencia' => '#3b82f6',
            'qr'            => '#a855f7',
            'tarjeta'       => '#f97316',
        ];

        $labels = $rows->map(fn ($r) => ucfirst((string) $r->metodo_pago))->all();
        $data   = $rows->map(fn ($r) => (float) $r->total)->all();
        $bgs    = $rows->map(fn ($r) => $colors[strtolower((string) $r->metodo_pago)] ?? '#6b7280')->all();

        return [
            'datasets' => [[
                'label' => 'Total (Bs.)',
                'data' => $data,
                'backgroundColor' => $bgs,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
