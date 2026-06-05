<?php

namespace App\Filament\Widgets;

use App\Models\SuscripcionesCobros;
use App\Support\ActiveInfraestructura;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class CobrosPorEstadoChart extends ChartWidget
{
    protected ?string $heading = 'Cobros por Estado (últimos 6 meses)';
    protected static ?int $sort = 3;

    public ?int $activeInfraId = null;

    public function mount(): void
    {
        $this->activeInfraId = ActiveInfraestructura::getDashboardId();
    }

    #[On('dashboardInfraChanged')]
    public function updateInfraFilter(?int $infraId = null): void
    {
        $this->activeInfraId = $infraId ?: null;
    }

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
            // Utilizar startOfMonth antes de subMonths para evitar desbordamientos de fecha
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $m->translatedFormat('M Y');
            $months[] = [$m->year, $m->month];
        }

        $datasets = [];
        foreach ($estados as $estado => $color) {
            $data = [];
            foreach ($months as [$year, $month]) {
                $query = SuscripcionesCobros::where('estado', $estado)
                    ->whereYear('fecha_vencimiento', $year)
                    ->whereMonth('fecha_vencimiento', $month);
                if ($this->activeInfraId) {
                    $query->whereHas('suscripcion.infraestructurasTienda.piso',
                        fn ($q) => $q->where('infraestructura_id', $this->activeInfraId)
                    );
                }
                $data[] = (int) $query->count();
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
