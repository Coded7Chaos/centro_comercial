<?php

namespace App\Filament\Resources\SuscripcionesTarifas\Pages;

use App\Filament\Resources\SuscripcionesTarifas\SuscripcionesTarifasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSuscripcionesTarifas extends CreateRecord
{
    protected static string $resource = SuscripcionesTarifasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['infraestructura_id'] = \App\Support\ActiveInfraestructura::getId();
        return $data;
    }
}
