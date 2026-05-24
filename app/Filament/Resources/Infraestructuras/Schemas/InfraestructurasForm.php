<?php

namespace App\Filament\Resources\Infraestructuras\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InfraestructurasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // NOMBRE
                TextInput::make('nombre')
                    ->required()
                    ->regex('/^[\pL\s]+$/u')
                    ->columnSpanFull(),

                // PISOS
                TextInput::make('pisos')
                    ->required()
                    ->numeric()
                    ->columnSpanFull(),

                // DIRECCIÓN (INPUT PRINCIPAL)
                TextInput::make('ubicacion')
                    ->label('Ubicación')
                    ->required()
                    ->live(debounce: 800) // IMPORTANTE para el JS reactivo
                    ->extraInputAttributes([
                        'id' => 'ubicacion',
                    ])
                    ->columnSpanFull(),


                // LATITUD (OCULTO FUNCIONAL)
                TextInput::make('lat')
                    ->required()
                    ->readOnly()
                    ->extraInputAttributes([
                        'id' => 'sucursal_lat',
                    ])
                    ->columnSpanFull(),


                // LONGITUD (OCULTO FUNCIONAL)
                TextInput::make('long')
                    ->required()
                    ->readOnly()
                    ->extraInputAttributes([
                        'id' => 'sucursal_long',
                    ])
                    ->columnSpanFull(),

                // MAPA INTERACTIVO
                Section::make('Ubicación en el mapa')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextInput::make('lat')
                                    ->label('Latitud')
                                    ->readOnly()
                                    ->extraInputAttributes([
                                        'id' => 'sucursal_lat',
                                    ]),

                                TextInput::make('long')
                                    ->label('Longitud')
                                    ->readOnly()
                                    ->extraInputAttributes([
                                        'id' => 'sucursal_long',
                                    ]),
                            ]),

                        ViewField::make('mapa')
                            ->label('Mapa interactivo')
                            ->view('filament.forms.components.leaflet-map')
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}