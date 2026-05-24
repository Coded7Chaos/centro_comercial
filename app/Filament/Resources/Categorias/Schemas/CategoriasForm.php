<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategoriasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // NOMBRE
                TextInput::make('nombre')
                    ->label('Nombre de la categoría')
                    ->required()

                    ->live()
                    ->debounce(500)

                    ->rule('regex:/^[\pL\s]+$/u')

                    ->validationMessages([
                        'required' => 'El nombre es obligatorio.',
                        'regex' => 'El nombre solo puede contener letras y espacios.',
                        'unique' => 'Ya existe una categoría con ese nombre.',
                    ])

                    ->maxLength(255)

                    ->unique(ignoreRecord: true),

                // DESCRIPCIÓN
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull()
                    ->nullable()

                    // VALIDACIÓN EN TIEMPO REAL
                    ->live()
                    ->debounce(500)

                    // DEBE CONTENER AL MENOS UNA LETRA
                    // PERMITE NÚMEROS Y CARACTERES BÁSICOS
                    ->rule('regex:/^(?=.*[\pL])[\pL\pN\s.,#\-()]+$/u')

                    ->validationMessages([
                        'regex' => 'La descripción debe contener al menos una letra.',
                        'min_length' => 'La descripción debe tener mínimo 5 caracteres.',
                    ])

                    ->minLength(5),

                // TIPO
                Select::make('tipo')
                    ->label('Tipo de categoría')
                    ->required()

                    ->options([
                        'categoria' => 'Categoría',
                        'subcategoria' => 'Subcategoría',
                    ])

                    ->default('categoria')

                    ->reactive()

                    // SI CAMBIA A CATEGORÍA, LIMPIA EL PADRE
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'categoria') {
                            $set('categoria_padre_id', null);
                        }
                    }),

                // CATEGORÍA PADRE
                Select::make('categoria_padre_id')
                    ->label('Categoría padre')

                    ->relationship(
                        name: 'padre',
                        titleAttribute: 'nombre',

                        // SOLO MUESTRA CATEGORÍAS
                        modifyQueryUsing: fn($query) =>
                        $query->where('tipo', 'categoria')
                    )

                    ->searchable()
                    ->preload()

                    // SOLO SI ES SUBCATEGORÍA
                    ->visible(fn($get) => $get('tipo') === 'subcategoria')

                    ->required(fn($get) => $get('tipo') === 'subcategoria')

                    ->nullable(),

                // ESTADO
                Select::make('estado')
                    ->label('Estado')
                    ->required()

                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ])

                    ->default('activo'),
            ]);
    }
}
