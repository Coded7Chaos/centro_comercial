<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\User;

class ClientesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // REQUERIDOS
                TextInput::make('nombres')
                    ->label('Nombres')
                    ->required()
                    ->maxLength(150)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->user?->nombres)),

                TextInput::make('apellido_paterno')
                    ->label('Apellido Paterno')
                    ->required()
                    ->maxLength(100)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->user?->apellido_paterno)),

                TextInput::make('ci')
                    ->label('CI')
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn($rule) => $rule->whereNull('deleted_at'))
                    ->numeric(),

                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->required()
                    ->unique(table: User::class, column: 'email', ignorable: fn($record) => $record?->user, modifyRuleUsing: fn($rule) => $rule->whereNull('deleted_at'))
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->user?->email)),

                Select::make('codigo_pais')
                    ->label('País')
                    ->default('+591')
                    ->options(['+591' => '🇧🇴 +591', '+54' => '🇦🇷 +54', '+55' => '🇧🇷 +55', '+56' => '🇨🇱 +56', '+1' => '🇺🇸 +1'])
                    ->native(false),

                TextInput::make('numero_celular')
                    ->label('Número Celular')
                    ->required()
                    ->numeric(),

                // OPCIONALES
                TextInput::make('apellido_materno')
                    ->label('Apellido Materno')
                    ->maxLength(100)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->user?->apellido_materno)),

                Select::make('genero')
                    ->label('Género')
                    ->placeholder('Seleccionar...')
                    ->options(['masculino' => 'Masculino', 'femenino' => 'Femenino'])
                    ->native(false),

                TextInput::make('correo_secundario')
                    ->label('Correo secundario')
                    ->email(),
            ]);
    }
}
