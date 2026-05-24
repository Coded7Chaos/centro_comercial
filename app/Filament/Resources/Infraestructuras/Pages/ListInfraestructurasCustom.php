<?php

namespace App\Filament\Resources\Infraestructuras\Pages;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use App\Models\Infraestructuras;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ListInfraestructurasCustom extends Page
{
    protected static string $resource = InfraestructurasResource::class;

    protected string $view = 'filament.resources.infraestructuras.pages.list-infraestructuras-custom';

    protected static ?string $title = 'Infraestructuras';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('ViewAny:Infraestructuras') ?? false;
    }

    public function getInfraestructuras()
    {
        return Infraestructuras::with(['pisosInfraestructura.tiendas.cliente.user', 'pisosInfraestructura.tiendas.marcas'])->latest()->get();
    }

    public function deleteInfraestructura($id)
    {
        try {
            $infra = Infraestructuras::findOrFail($id);
            
            // Las relaciones se borrarán según las FKs (cascade) o manualmente si es necesario
            $infra->delete();

            Notification::make()
                ->title('Infraestructura eliminada')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al eliminar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
