<?php

namespace App\Filament\Resources\SuscripcionesCobros\Pages;

use App\Filament\Resources\SuscripcionesCobros\SuscripcionesCobrosResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSuscripcionesCobros extends EditRecord
{
    protected static string $resource = SuscripcionesCobrosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
