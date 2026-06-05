<?php

namespace App\Filament\Resources\SuscripcionesCobros\Pages;

use App\Filament\Resources\SuscripcionesCobros\SuscripcionesCobrosResource;
use App\Filament\Widgets\CobrosMonthNavigator;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListSuscripcionesCobros extends ListRecords
{
    protected static string $resource = SuscripcionesCobrosResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CobrosMonthNavigator::class,
        ];
    }

    #[On('cobrosNavChanged')]
    public function refreshOnNavChange(int $mes, int $anio): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(function ($query) {
                $mes = (int) session('cobros_nav_mes', now()->month);
                $anio = (int) session('cobros_nav_anio', now()->year);

                return $query
                    ->whereMonth('fecha_vencimiento', $mes)
                    ->whereYear('fecha_vencimiento', $anio);
            });
    }

    public function getTabs(): array
    {
        return [
            'mensuales' => Tab::make('Cobros mensuales')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('es_parcial', false)
                    ->whereNotIn('estado', ['pagado', 'anulado'])
                    ->where('fecha_vencimiento', '>=', now()->toDateString())
                ),
            'morosos' => Tab::make('Morosos')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('es_parcial', false)
                    ->whereNotIn('estado', ['pagado', 'anulado'])
                    ->where('fecha_vencimiento', '<', now()->toDateString())
                ),
            'parciales' => Tab::make('Cobros parciales')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('es_parcial', true)
                    ->whereNotIn('estado', ['pagado', 'anulado'])
                ),
            'todos' => Tab::make('Todos los cobros')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNotIn('estado', ['anulado'])
                ),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'mensuales';
    }
}
