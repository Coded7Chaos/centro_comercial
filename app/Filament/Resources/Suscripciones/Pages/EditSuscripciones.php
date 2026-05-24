<?php

namespace App\Filament\Resources\Suscripciones\Pages;

use App\Filament\Resources\Suscripciones\SuscripcionesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSuscripciones extends EditRecord
{
    protected static string $resource = SuscripcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
