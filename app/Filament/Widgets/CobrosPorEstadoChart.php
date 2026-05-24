<?php

namespace App\Filament\Widgets;

use App\Models\SuscripcionesCobros;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CobrosPorEstadoChart extends ChartWidget
{
    protected ?string $heading = 'Cobros por Estado (últimos 6 meses)';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:CobrosPorEstadoChart') ?? false;
    }

    protected function getData(): array
    {
        $estados = [
            'pagado'    => '#16a34a',
            'parcial'   => '#eab308',
            'pendiente' => '#3b82f6',
            'vencido'   => '#dc2626',
            'anulado'   => '#6b7280',
        ];

        $labels = [];
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $labels[] = $m->translatedFormat('M Y');
            $months[] = [$m->year, $m->month];
        }

        $datasets = [];
        foreach ($estados as $estado => $color) {
            $data = [];
            foreach ($months as [$year, $month]) {
                $data[] = (int) SuscripcionesCobros::where('estado', $estado)
                    ->whereYear('fecha_vencimiento', $year)
                    ->whereMonth('fecha_vencimiento', $month)
                    ->count();
            }
            $datasets[] = [
                'label' => ucfirst($estado),
                'data' => $data,
                'backgroundColor' => $color,
                'stack' => 'cobros',
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true, 'beginAtZero' => true],
            ],
        ];
    }
}
