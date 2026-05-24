<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SuscripcionesPagos;
use Illuminate\Support\Carbon;

class IngresosMensualesChart extends ChartWidget
{
    protected ?string $heading = 'Ingresos por Pagos (Últimos 6 meses)';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:IngresosMensualesChart') ?? false;
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            
            $sum = SuscripcionesPagos::whereMonth('fecha_pago', $month->month)
                ->whereYear('fecha_pago', $month->year)
                ->sum('monto_pagado');
                
            $data[] = $sum;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos (Bs)',
                    'data' => $data,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)', // blue-500
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
