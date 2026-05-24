<?php

namespace App\Filament\Resources\SuscripcionesPagos\Pages;

use App\Filament\Resources\SuscripcionesPagos\SuscripcionesPagosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuscripcionesPagos extends ListRecords
{
    protected static string $resource = SuscripcionesPagosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
