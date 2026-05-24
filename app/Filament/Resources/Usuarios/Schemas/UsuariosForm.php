<?php

namespace App\Filament\Resources\Usuarios\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class UsuariosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombres')
                    ->label('Nombres')
                    ->regex('/^[\pL\s]+$/u')
                    ->required(),
                TextInput::make('apellido_paterno')
                    ->label('Apellido Paterno')
                    ->regex('/^[\pL\s]+$/u')
                    ->required(),
                TextInput::make('apellido_materno')
                    ->label('Apellido Materno')
                    ->regex('/^[\pL\s]+$/u')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('roles')
                    ->label('Rol')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereIn('name', ['admin', 'super_admin']),
                    )
                    ->preload()
                    ->required(),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    // Obligatorio solo al crear
                    ->required(fn(string $operation): bool => $operation === 'create')
                    // Confirmación
                    ->same('password_confirmation')
                    // Solo guardar si tiene contenido
                    ->dehydrated(fn($state) => filled($state))
                    // Encriptar contraseña
                    ->dehydrateStateUsing(fn($state) => Hash::make($state)),

                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->password()
                    // Obligatorio solo al crear o si escribió password
                    ->required(fn(string $operation, $get): bool => $operation === 'create' || filled($get('password')))
                    // No guardar en BD
                    ->dehydrated(false),
            ]);
    }
}
