<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SuscripcionesPagos;
use App\Support\ActiveInfraestructura;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class IngresosMensualesChart extends ChartWidget
{
    protected ?string $heading = 'Ingresos por Pagos (Últimos 6 meses)';
    protected static ?int $sort = 2;

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
        return auth()->user()?->can('View:IngresosMensualesChart') ?? false;
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');

            $query = SuscripcionesPagos::where('estado_verificacion', 'verificado')->whereBetween('fecha_pago', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ]);
            if ($this->activeInfraId) {
                $query->whereHas('cobro.suscripcion.infraestructurasTienda.piso',
                    fn ($q) => $q->where('infraestructura_id', $this->activeInfraId)
                );
            }
            $data[] = (float) $query->sum('monto_pagado');
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
