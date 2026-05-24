<?php

namespace App\Filament\Resources\SuscripcionesCobros\Pages;

use App\Filament\Resources\SuscripcionesCobros\SuscripcionesCobrosResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSuscripcionesCobros extends ViewRecord
{
    protected static string $resource = SuscripcionesCobrosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
