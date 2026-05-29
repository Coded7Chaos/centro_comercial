<?php

namespace App\Filament\Resources\Marcas\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MarcasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // LOGO
                FileUpload::make('logo')
                    ->label('Logo de la marca')
                    ->image()
                    ->directory('marcas-logos')
                    ->disk('public')
                    ->imageEditor()

                    // SOLO IMÁGENES
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/jpg',
                    ])

                    ->maxSize(2048)

                    ->helperText(
                        'Solo imágenes JPG, PNG o WEBP. Máximo 2MB.'
                    )

                    ->columnSpanFull(),

                // NOMBRE
                TextInput::make('nombre')
                    ->required()

                    // ENTRE 3 Y 60 CARACTERES
                    ->minLength(3)
                    ->maxLength(60)

                    // DEBE CONTENER LETRAS
                    // ACEPTA LETRAS, NÚMEROS, ESPACIOS Y ALGUNOS SÍMBOLOS
                    ->regex('/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\&\.]{3,60}$/u')

                    ->validationMessages([
                        'regex' =>
                        'El nombre debe contener letras.',
                    ])

                    ->unique(ignoreRecord: true),

                // CLIENTE
                Select::make('cliente_id')
                    ->label('Propietario / Cliente (Dejar vacío para hacer esta marca Global)')
                    ->relationship('cliente', 'ci')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre_completo)
                    ->searchable(['ci', 'user.nombres', 'user.apellido_paterno', 'user.apellido_materno'])
                    ->preload()
                    ->nullable(),

                // DESCRIPCIÓN
                Textarea::make('descripcion')

                    ->rows(4)

                    ->maxLength(500)

                    // DEBE CONTENER LETRAS SI O SI
                    ->regex('/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ]).*$/u')

                    ->validationMessages([
                        'regex' =>
                        'La descripción debe contener al menos una letra.',
                    ])

                    ->columnSpanFull(),

                // ESTADO
                Select::make('estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ])
                    ->default('activo')
                    ->required(),
            ]);
    }
}