<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Infraestructuras;
use App\Support\ActiveInfraestructura;

class SeleccionarInfraestructura extends Page
{
    protected string $view = 'filament.pages.seleccionar-infraestructura';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->hasRole('admin') || $user?->hasRole('super_admin');
    }

    public function selectInfraestructura(int $id): void
    {
        ActiveInfraestructura::setId($id);
        $this->redirect(route('filament.admin.pages.dashboard'));
    }

    protected function getViewData(): array
    {
        return [
            'infraestructuras' => Infraestructuras::with('pisosInfraestructura.tiendas')
                ->orderBy('nombre')
                ->get(),
            'activeId' => ActiveInfraestructura::getId(),
        ];
    }
}
