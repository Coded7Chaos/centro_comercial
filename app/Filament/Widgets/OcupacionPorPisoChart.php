<?php

namespace App\Filament\Widgets;

use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Support\ActiveInfraestructura;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class OcupacionPorPisoChart extends ChartWidget
{
    protected ?string $heading = 'Ocupación por Piso';
    protected static ?int $sort = 4;

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
        return auth()->user()?->can('View:OcupacionPorPisoChart') ?? false;
    }

    protected function getData(): array
    {
        $pisosQuery = $this->activeInfraId
            ? InfraestructurasPisos::where('infraestructura_id', $this->activeInfraId)
            : InfraestructurasPisos::query();
        $pisos = $pisosQuery->orderBy('id')->get();

        $labels = [];
        $ocupadas = [];
        $disponibles = [];

        foreach ($pisos as $piso) {
            $displayNumero = $piso->numero;
            if ($displayNumero && is_numeric($displayNumero)) {
                $displayNumero = 'Piso ' . $displayNumero;
            }
            $labels[] = ($displayNumero ? $displayNumero . ' - ' . $piso->nombre : $piso->nombre) ?? ('Piso #' . $piso->id);

            $totalPiso = InfraestructurasTiendas::where('infraestructura_piso_id', $piso->id)->count();
            $alquiladas = InfraestructurasTiendas::where('infraestructura_piso_id', $piso->id)
                ->whereHas('estado', fn ($q) => $q->where('estado', 'Alquilada'))
                ->count();

            $ocupadas[] = $alquiladas;
            $disponibles[] = max(0, $totalPiso - $alquiladas);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Alquiladas',
                    'data' => $ocupadas,
                    'backgroundColor' => '#16a34a',
                ],
                [
                    'label' => 'Libres',
                    'data' => $disponibles,
                    'backgroundColor' => '#94a3b8',
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
                'x' => ['stacked' => true, 'beginAtZero' => true],
                'y' => ['stacked' => true],
            ],
        ];
    }
}
