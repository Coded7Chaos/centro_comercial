<?php

namespace App\Filament\Resources\Suscripciones\Pages;

use App\Filament\Resources\Suscripciones\SuscripcionesResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSuscripciones extends ViewRecord
{
    protected static string $resource = SuscripcionesResource::class;

    protected string $view = 'filament.pages.ver-suscripcion';
}
