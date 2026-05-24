<?php

namespace App\Filament\Resources\SuscripcionesPagos\Pages;

use App\Filament\Resources\SuscripcionesPagos\SuscripcionesPagosResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSuscripcionesPagos extends EditRecord
{
    protected static string $resource = SuscripcionesPagosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
