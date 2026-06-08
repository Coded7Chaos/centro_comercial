<?php

namespace App\Filament\Resources\Suscripciones\Pages;

use App\Filament\Resources\Suscripciones\SuscripcionesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSuscripciones extends ListRecords
{
    protected static string $resource = SuscripcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(route('admin.suscripciones.crear-custom')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('fecha_fin', '>=', now()->toDateString())),
            'pasados' => Tab::make('Pasados')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('fecha_fin', '<', now()->toDateString())),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'activos';
    }
}
