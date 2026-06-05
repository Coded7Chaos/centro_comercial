<?php

namespace App\Filament\Resources\Categorias\Pages;

use App\Filament\Resources\Categorias\CategoriasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategorias extends CreateRecord
{
    protected static string $resource = CategoriasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['infraestructura_id'] = \App\Support\ActiveInfraestructura::getId();
        return $data;
    }
}
