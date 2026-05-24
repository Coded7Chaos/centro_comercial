<?php

namespace App\Filament\Resources\SuscripcionesPagos\Pages;

use App\Filament\Resources\SuscripcionesPagos\SuscripcionesPagosResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSuscripcionesPagos extends ViewRecord
{
    protected static string $resource = SuscripcionesPagosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
