<?php

namespace App\Filament\Widgets;

use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use Filament\Widgets\ChartWidget;

class CostoOportunidadVacanciaChart extends ChartWidget
{
    protected ?string $heading = 'Costo de Oportunidad por Vacancia (Pérdidas)';
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->user()?->can('View:CostoOportunidadVacanciaChart') ?? false;
    }

    protected function getData(): array
    {
        $pisos = InfraestructurasPisos::orderBy('id')->get();

        $labels = [];
        $costoOportunidad = [];

        foreach ($pisos as $piso) {
            $labels[] = $piso->nombre ?? ('Piso #' . $piso->id);

            $tiendasLibres = InfraestructurasTiendas::where('infraestructura_piso_id', $piso->id)
                ->whereHas('estado', fn ($q) => $q->where('estado', 'Disponible'))
                ->get();

            $totalPerdidaPiso = 0;
            foreach ($tiendasLibres as $tienda) {
                $totalPerdidaPiso += $tienda->getCostoOportunidad();
            }

            $costoOportunidad[] = $totalPerdidaPiso;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pérdidas Estimadas (Bs.)',
                    'data' => $costoOportunidad,
                    'backgroundColor' => '#f43f5e',
                ],
            ],
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
            'indexAxis' => 'y',
            'scales' => [
                'x' => ['beginAtZero' => true],
            ],
        ];
    }
}
