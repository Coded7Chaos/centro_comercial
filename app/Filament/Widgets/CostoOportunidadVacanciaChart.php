<?php

namespace App\Filament\Widgets;

use App\Models\InfraestructurasTiendas;
use App\Models\SuscripcionesTarifas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CostoOportunidadVacanciaChart extends ChartWidget
{
    protected ?string $heading = 'Costo de Oportunidad por Vacancia (Pérdidas Acumuladas 30 días)';
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:CostoOportunidadVacanciaChart') ?? false;
    }

    protected function getData(): array
    {
        $tiendasDisponibles = InfraestructurasTiendas::whereHas('estado', fn ($q) => $q->where('estado', 'Disponible'))->get();

        $labels = [];
        $dataMin = [];
        $dataMax = [];

        // Generar últimos 30 días
        $fechas = [];
        for ($i = 29; $i >= 0; $i--) {
            $fechas[] = Carbon::now()->subDays($i);
        }

        $acumuladoMin = 0;
        $acumuladoMax = 0;

        foreach ($fechas as $fecha) {
            $labels[] = $fecha->format('d/m');
            
            $perdidaDiaMin = 0;
            $perdidaDiaMax = 0;

            foreach ($tiendasDisponibles as $tienda) {
                // Si la tienda ya estaba vacante en esa fecha
                if ($tienda->created_at->isBefore($fecha) || $tienda->created_at->isSameDay($fecha)) {
                    $tamano = (float) $tienda->tamano;
                    
                    // Cálculo de tarifas
                    $tarifas1Mes = SuscripcionesTarifas::calcularAlquiler($tamano, 1);
                    $tarifas12Meses = SuscripcionesTarifas::calcularAlquiler($tamano, 12);

                    $maxDia = $tarifas1Mes['precio_mensual_base'] / 30;
                    $minDia = $tarifas12Meses['precio_mensual_con_descuento'] / 30;

                    $perdidaDiaMax += $maxDia;
                    $perdidaDiaMin += $minDia;
                }
            }

            $acumuladoMax += $perdidaDiaMax;
            $acumuladoMin += $perdidaDiaMin;

            $dataMax[] = round($acumuladoMax, 2);
            $dataMin[] = round($acumuladoMin, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pérdida Máxima Estimada (Bs.)',
                    'data' => $dataMax,
                    'borderColor' => '#f43f5e',
                    'backgroundColor' => '#f43f5e33',
                    'fill' => 'origin',
                ],
                [
                    'label' => 'Pérdida Mínima Estimada (Bs.)',
                    'data' => $dataMin,
                    'borderColor' => '#fbbf24',
                    'backgroundColor' => '#fbbf2433',
                    'fill' => '-1', // Rellena el espacio entre las dos líneas
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Bs. Acumulados',
                    ],
                ],
            ],
        ];
    }
}
