<?php

namespace App\Filament\Widgets;

use App\Models\SuscripcionesPagos;
use App\Models\SuscripcionesCobros;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can('View:StatsOverview') ?? false;
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $inicioMes = $now->copy()->startOfMonth();
        $inicioMesPasado = $now->copy()->subMonth()->startOfMonth();
        $finMesPasado = $now->copy()->subMonth()->endOfMonth();

        $ingresosMes = (float) SuscripcionesPagos::whereBetween('fecha_pago', [$inicioMes, $now])->sum('monto_pagado');
        $ingresosMesAnterior = (float) SuscripcionesPagos::whereBetween('fecha_pago', [$inicioMesPasado, $finMesPasado])->sum('monto_pagado');

        $variacion = $ingresosMesAnterior > 0
            ? round((($ingresosMes - $ingresosMesAnterior) / $ingresosMesAnterior) * 100, 1)
            : null;

        $deudaVencida = (float) SuscripcionesCobros::whereIn('estado', ['vencido', 'parcial'])
            ->whereDate('fecha_vencimiento', '<', $now->toDateString())
            ->with('pagos')
            ->get()
            ->sum(fn ($c) => max(0, (float) $c->monto - (float) $c->pagos->sum('monto_pagado')));

        $tiendasTotal = InfraestructurasTiendas::count();
        $tiendasOcupadas = InfraestructurasTiendas::whereHas('estado', function ($q) {
            $q->where('estado', 'Alquilada');
        })->count();
        $ocupacion = $tiendasTotal > 0 ? round(($tiendasOcupadas / $tiendasTotal) * 100, 1) : 0;

        $contratosPorVencer = Suscripciones::whereBetween('fecha_fin', [
            $now->toDateString(),
            $now->copy()->addDays(30)->toDateString(),
        ])->count();

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
