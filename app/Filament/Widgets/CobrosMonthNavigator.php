<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;

class CobrosMonthNavigator extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.cobros-month-navigator';

    public int $mes;

    public int $anio;

    public bool $sinFiltro = false;

    public function mount(): void
    {
        $this->mes = (int) session('cobros_nav_mes', now()->month);
        $this->anio = (int) session('cobros_nav_anio', now()->year);
        $this->sinFiltro = (bool) session('cobros_nav_sin_filtro', false);
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->anio, $this->mes, 1)->subMonthNoOverflow();
        $this->mes = $date->month;
        $this->anio = $date->year;
        $this->sync();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->anio, $this->mes, 1)->addMonthNoOverflow();
        $this->mes = $date->month;
        $this->anio = $date->year;
        $this->sync();
    }

    public function currentMonth(): void
    {
        $this->mes = now()->month;
        $this->anio = now()->year;
        $this->sync();
    }

    public function updatedMes(): void
    {
        $this->mes = (int) $this->mes;
        $this->sync();
    }

    public function updatedAnio(): void
    {
        $this->anio = (int) $this->anio;
        $this->sync();
    }

    public function limpiarFiltro(): void
    {
        $this->sinFiltro = true;
        session(['cobros_nav_sin_filtro' => true]);
        $this->dispatch('cobrosNavChanged', mes: $this->mes, anio: $this->anio);
    }

    private function sync(): void
    {
        $this->sinFiltro = false;
        session([
            'cobros_nav_mes' => $this->mes,
            'cobros_nav_anio' => $this->anio,
            'cobros_nav_sin_filtro' => false,
        ]);
        $this->dispatch('cobrosNavChanged', mes: $this->mes, anio: $this->anio);
    }

    protected function getViewData(): array
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo',  6 => 'Junio',   7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $currentYear = now()->year;
        $anios = range($currentYear - 3, $currentYear + 2);

        return [
            'meses' => $meses,
            'anios' => array_combine($anios, $anios),
            'mesNombreActual' => $meses[$this->mes].' '.$this->anio,
        ];
    }
}
