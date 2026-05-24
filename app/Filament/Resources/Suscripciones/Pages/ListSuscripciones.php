<?php

namespace App\Filament\Resources\Suscripciones\Pages;

use App\Filament\Resources\Suscripciones\SuscripcionesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuscripciones extends ListRecords
{
    protected static string $resource = SuscripcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
