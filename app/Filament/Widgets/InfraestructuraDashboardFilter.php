<?php

namespace App\Filament\Widgets;

use App\Models\Infraestructuras;
use App\Support\ActiveInfraestructura;
use Filament\Widgets\Widget;

class InfraestructuraDashboardFilter extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -1;

    protected string $view = 'filament.widgets.infraestructura-dashboard-filter';

    protected int|string|array $columnSpan = 'full';

    public ?int $selectedInfraId = null;

    public function mount(): void
    {
        $this->selectedInfraId = ActiveInfraestructura::getDashboardId();
    }

    public function updatedSelectedInfraId(): void
    {
        ActiveInfraestructura::setDashboardId($this->selectedInfraId ?: null);
        $this->dispatch('dashboardInfraChanged', infraId: $this->selectedInfraId);
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('admin') || $user?->hasRole('super_admin');
    }

    protected function getViewData(): array
    {
        return [
            'infraestructuras' => Infraestructuras::orderBy('nombre')->get(),
            'activeLabel' => $this->selectedInfraId
                ? Infraestructuras::find($this->selectedInfraId)?->nombre
                : 'Todas las infraestructuras',
        ];
    }
}
