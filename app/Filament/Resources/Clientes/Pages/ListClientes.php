<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClientesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListClientes extends ListRecords
{
    protected static string $resource = ClientesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
            'bloqueados' => Tab::make('Eliminados/Bloqueados')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'activos';
    }
}
