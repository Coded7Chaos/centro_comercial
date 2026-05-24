<?php

namespace App\Filament\Resources\Infraestructuras\Pages;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInfraestructuras extends ListRecords
{
    protected static string $resource = InfraestructurasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
