<?php

namespace App\Filament\Resources\SuscripcionesTarifas\Schemas;

use App\Models\SuscripcionesTarifas;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SuscripcionesTarifasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tamano_min')
                ->label('Tamaño mínimo (m²)')
                ->numeric()
                ->minValue(0)
                ->required(),
            TextInput::make('tamano_max')
                ->label('Tamaño máximo (m²)')
                ->numeric()
                ->minValue(0)
                ->required()
                ->rules([
                    fn (\Filament\Schemas\Components\Utilities\Get $get) =>
                        'gte:' . (float) ($get('tamano_min') ?? 0),
                ]),
            TextInput::make('etiqueta')
                ->label('Etiqueta visible (Pequeño, Mediano, Grande...)')
                ->maxLength(60),
            Select::make('tipo')
                ->label('Tipo de suscripción')
                ->options(SuscripcionesTarifas::tipos())
                ->required(),
            TextInput::make('precio')
                ->label('Precio')
                ->numeric()
                ->prefix('Bs.')
                ->required(),
        ])->columns(2);
    }
}
