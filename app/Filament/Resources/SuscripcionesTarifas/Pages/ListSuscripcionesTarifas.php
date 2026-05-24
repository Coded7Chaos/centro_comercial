<?php

namespace App\Filament\Resources\SuscripcionesTarifas\Pages;

use App\Filament\Resources\SuscripcionesTarifas\SuscripcionesTarifasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuscripcionesTarifas extends ListRecords
{
    protected static string $resource = SuscripcionesTarifasResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
