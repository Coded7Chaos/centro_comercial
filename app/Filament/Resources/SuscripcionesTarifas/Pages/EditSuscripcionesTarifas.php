<?php

namespace App\Filament\Resources\SuscripcionesTarifas\Pages;

use App\Filament\Resources\SuscripcionesTarifas\SuscripcionesTarifasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSuscripcionesTarifas extends EditRecord
{
    protected static string $resource = SuscripcionesTarifasResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
