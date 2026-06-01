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
            // Utilizar startOfMonth para evitar desbordamientos de fecha en el cálculo
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $labels[] = $month->translatedFormat('M Y');
            
            // Sumar los pagos dentro del rango completo del mes (independientemente del formato/timezone)
            $sum = SuscripcionesPagos::whereBetween('fecha_pago', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString()
            ])->sum('monto_pagado');
                
            $data[] = (float) $sum;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos (Bs)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)', // blue-500 con más opacidad para barras
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
