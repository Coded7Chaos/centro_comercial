<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClientesResource;
use Filament\Resources\Pages\ViewRecord;

class ViewClienteCustom extends ViewRecord
{
    protected static string $resource = ClientesResource::class;

    protected string $view = 'filament.pages.ver-cliente';
}
