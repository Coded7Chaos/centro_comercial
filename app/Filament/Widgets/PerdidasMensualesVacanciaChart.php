<?php

namespace App\Filament\Widgets;

use App\Models\InfraestructurasTiendas;
use App\Models\SuscripcionesTarifas;
use App\Support\ActiveInfraestructura;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class PerdidasMensualesVacanciaChart extends ChartWidget
{
    protected ?string $heading = 'Pérdidas Mensuales por Vacancia';
    protected static ?int $sort = 6;
    protected string $view = 'filament.widgets.costo-oportunidad-vacancia-chart';
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

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
        return auth()->user()?->can('View:PerdidasMensualesVacanciaChart') ?? false;
    }

    protected function getData(): array
    {
        $tiendasQuery = InfraestructurasTiendas::whereHas('estado', fn ($q) => $q->where('estado', 'Disponible'));

        if ($this->activeInfraId) {
            $tiendasQuery->whereHas('piso', fn ($q) => $q->where('infraestructura_id', $this->activeInfraId));
        }

        $tiendasDisponibles = $tiendasQuery->with('piso.infraestructura')->get();

        if ($tiendasDisponibles->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Pérdida Mensual Estimada (Bs.)',
                        'data' => [],
                        'borderColor' => '#f43f5e',
                        'backgroundColor' => '#f43f5e33',
                        'fill' => false,
                    ],
                ],
                'labels' => [],
            ];
        }

        $vacancyStarts = $tiendasDisponibles
            ->map(fn (InfraestructurasTiendas $tienda) => $tienda->getFechaLibreDesde()->startOfDay())
            ->filter(fn (Carbon $date) => $date->lte(Carbon::now()->startOfDay()));

        if ($vacancyStarts->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Pérdida Mensual Estimada (Bs.)',
                        'data' => [0],
                        'borderColor' => '#f43f5e',
                        'backgroundColor' => '#f43f5e33',
                        'fill' => false,
                    ],
                ],
                'labels' => [Carbon::now()->translatedFormat('M Y')],
            ];
        }

        $periodos = [];
        $startDate = $vacancyStarts->min()->startOfMonth();
        $endDate = Carbon::now()->startOfDay();

        for ($fecha = $startDate->copy(); $fecha->lte($endDate); $fecha->addMonthNoOverflow()) {
            $periodos[] = [
                'inicio' => $fecha->copy()->startOfMonth(),
                'fin' => $fecha->copy()->endOfMonth()->min($endDate),
            ];
        }

        $labels = [];
        $data = [];

        foreach ($periodos as $periodo) {
            $labels[] = $periodo['inicio']->translatedFormat('M Y');
            $perdidaMes = 0;

            foreach ($tiendasDisponibles as $tienda) {
                $fechaLibreDesde = $tienda->getFechaLibreDesde()->startOfDay();

                if ($fechaLibreDesde->gt($periodo['fin'])) {
                    continue;
                }

                $tamano = (float) $tienda->tamano;
                $tarifas1Mes = SuscripcionesTarifas::calcularAlquiler($tamano, 1);
                $precioReferencial = $tienda->getPrecioMensualReferencial();
                $precioMaximo = $tarifas1Mes['precio_mensual_base'] > 0
                    ? $tarifas1Mes['precio_mensual_base']
                    : $precioReferencial;

                $diasVacantesEnPeriodo = (int) floor(max(
                    0,
                    $fechaLibreDesde->max($periodo['inicio'])
                        ->diffInDays($periodo['fin']) + 1
                ));

                $perdidaMes += ($precioMaximo / 30) * $diasVacantesEnPeriodo;
            }

            $data[] = round($perdidaMes, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pérdida Mensual Estimada (Bs.)',
                    'data' => $data,
                    'borderColor' => '#f43f5e',
                    'backgroundColor' => '#f43f5e33',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'layout' => [
                'padding' => [
                    'left' => 12,
                    'right' => 120,
                    'top' => 38,
                    'bottom' => 18,
                ],
            ],
            'plugins' => [
                'permanentLabels' => [
                    'display' => true,
                    'yOffsets' => [-20],
                ],
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'autoSkip' => false,
                        'minRotation' => 0,
                        'maxRotation' => 0,
                        'padding' => 18,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'maxTicksLimit' => 7,
                        'padding' => 16,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Bs. del mes',
                    ],
                ],
            ],
        ];
    }
}
