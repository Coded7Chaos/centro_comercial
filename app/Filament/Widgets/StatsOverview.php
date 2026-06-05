<?php

namespace App\Filament\Widgets;

use App\Models\SuscripcionesPagos;
use App\Models\SuscripcionesCobros;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Support\ActiveInfraestructura;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class StatsOverview extends BaseWidget
{
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
        return auth()->user()?->can('View:StatsOverview') ?? false;
    }

    private function infraScope(\Illuminate\Database\Eloquent\Builder $query, string $chain): \Illuminate\Database\Eloquent\Builder
    {
        if (!$this->activeInfraId) return $query;
        return $query->whereHas($chain, fn ($q) => $q->where('infraestructura_id', $this->activeInfraId));
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $inicioMes = $now->copy()->startOfMonth();
        $inicioMesPasado = $now->copy()->startOfMonth()->subMonth();
        $finMesPasado = $now->copy()->startOfMonth()->subMonth()->endOfMonth();

        $pagosBase = fn() => $this->infraScope(SuscripcionesPagos::where('estado_verificacion', 'verificado'), 'cobro.suscripcion.infraestructurasTienda.piso');

        $ingresosMes = (float) $pagosBase()->whereBetween('fecha_pago', [$inicioMes, $now])->sum('monto_pagado');
        $ingresosMesAnterior = (float) $pagosBase()->whereBetween('fecha_pago', [$inicioMesPasado, $finMesPasado])->sum('monto_pagado');

        $variacion = $ingresosMesAnterior > 0
            ? round((($ingresosMes - $ingresosMesAnterior) / $ingresosMesAnterior) * 100, 1)
            : null;

        $cobrosQuery = $this->infraScope(
            SuscripcionesCobros::whereIn('estado', ['vencido', 'parcial'])
                ->whereDate('fecha_vencimiento', '<', $now->toDateString()),
            'suscripcion.infraestructurasTienda.piso'
        );
        $deudaVencida = (float) $cobrosQuery->with('pagos')->get()
            ->sum(fn ($c) => max(0, (float) $c->monto - (float) $c->pagos->where('estado_verificacion', 'verificado')->sum('monto_pagado')));

        $tiendasQuery   = $this->activeInfraId
            ? InfraestructurasTiendas::whereHas('piso', fn ($q) => $q->where('infraestructura_id', $this->activeInfraId))
            : InfraestructurasTiendas::query();
        $tiendasTotal   = (clone $tiendasQuery)->count();
        $tiendasOcupadas = (clone $tiendasQuery)->whereHas('estado', fn ($q) => $q->where('estado', 'Alquilada'))->count();
        $ocupacion = $tiendasTotal > 0 ? round(($tiendasOcupadas / $tiendasTotal) * 100, 1) : 0;

        $contratosQuery = $this->infraScope(
            Suscripciones::whereBetween('fecha_fin', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()]),
            'infraestructurasTienda.piso'
        );
        $contratosPorVencer = $contratosQuery->count();

        $stats = [
            Stat::make('Ingresos del mes', 'Bs. ' . number_format($ingresosMes, 2))
                ->description(
                    $variacion === null
                        ? 'Sin datos del mes anterior'
                        : ($variacion >= 0 ? "+{$variacion}% vs mes anterior" : "{$variacion}% vs mes anterior")
                )
                ->descriptionIcon($variacion === null || $variacion >= 0
                    ? 'heroicon-m-arrow-trending-up'
                    : 'heroicon-m-arrow-trending-down')
                ->color($variacion === null || $variacion >= 0 ? 'success' : 'danger'),

            Stat::make('Deuda vencida', 'Bs. ' . number_format($deudaVencida, 2))
                ->description('Cobros que pasaron su fecha límite')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Ocupación', $ocupacion . '%')
                ->description($tiendasOcupadas . ' de ' . $tiendasTotal . ' tiendas alquiladas')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make('Contratos por vencer', (string) $contratosPorVencer)
                ->description('En los próximos 30 días')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($contratosPorVencer > 0 ? 'warning' : 'gray'),
        ];

        return $stats;
    }
}
