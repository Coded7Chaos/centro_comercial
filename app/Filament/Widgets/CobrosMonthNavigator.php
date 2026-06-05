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

    public function mount(): void
    {
        $this->mes = (int) session('cobros_nav_mes', now()->month);
        $this->anio = (int) session('cobros_nav_anio', now()->year);
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

    private function sync(): void
    {
        session(['cobros_nav_mes' => $this->mes, 'cobros_nav_anio' => $this->anio]);
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
