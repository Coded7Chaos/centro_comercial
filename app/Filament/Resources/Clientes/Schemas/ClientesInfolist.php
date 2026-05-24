<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;

class ClientesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- CABECERA PREMIUM ---
                Section::make()
                    ->schema([
                        Grid::make(6)
                            ->schema([
                                Group::make([
                                    ImageEntry::make('foto')
                                        ->hiddenLabel()
                                        ->circular()
                                        ->height(100)
                                        ->width(100)
                                        ->defaultImageUrl('https://ui-avatars.com/api/?name=User&color=7F9CF5&background=EBF4FF'),
                                ])->columnSpan(1),
                                Group::make([
                                    TextEntry::make('user.full_name')
                                        ->hiddenLabel()
                                        ->weight('bold')
                                        ->size('lg')
                                        ->state(fn($record) => "{$record->user?->nombres} {$record->user?->apellido_paterno} {$record->user?->apellido_materno}"),
                                ])->columnSpan(5),
                            ]),
                    ])
                    ->extraAttributes(['class' => 'bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl border-none p-6']),

                Grid::make(2)
                    ->schema([
                        // --- COLUMNA IZQUIERDA: INFORMACIÓN PERSONAL ---
                        Section::make('Información personal')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('user.nombres')
                                            ->label('Nombres')
                                            ->icon('heroicon-o-user-circle'),
                                        
                                        TextEntry::make('user.apellidos')
                                            ->label('Apellidos')
                                            ->state(fn($record) => ($record->user?->apellido_paterno ?? '') . ' ' . ($record->user?->apellido_materno ?? ''))
                                            ->icon('heroicon-o-user-group'),

                                        TextEntry::make('ci')
                                            ->label('CI (Carnet de identidad)')
                                            ->icon('heroicon-o-identification'),

                                        TextEntry::make('genero')
                                            ->label('Género')
                                            ->placeholder('No definido')
                                            ->state(fn($state) => ucfirst($state ?? ''))
                                            ->icon('heroicon-o-variable'),

                                        TextEntry::make('user.email')
                                            ->label('Correo electrónico')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable(),

                                        TextEntry::make('numero_contacto')
                                            ->label('Número de contacto')
                                            ->state(fn($record) => ($record->codigo_pais ?? '+591') . ' ' . ($record->numero_celular ?? ''))
                                            ->icon('heroicon-o-phone'),

                                        TextEntry::make('correo_secundario')
                                            ->label('Correo secundario')
                                            ->placeholder('No definido')
                                            ->icon('heroicon-o-envelope-open'),

                                            TextEntry::make('created_at')
                                            ->label('Fecha de registro')
                                            ->dateTime('d \d\e F \d\e Y')
                                            ->icon('heroicon-o-calendar'),

                                        TextEntry::make('updated_at')
                                            ->label('Fecha de actualización')
                                            ->dateTime('d \d\e F \d\e Y')
                                            ->icon('heroicon-o-arrow-path'),
                                        
                                        TextEntry::make('tiendas.numero')
                                            ->label('Tiendas asociadas')
                                            ->badge()
                                            ->color('gray')
                                            ->state(fn($record) => $record->tiendas->map(fn($t) => $t->nombre ?: "Tienda #{$t->numero}")->toArray())
                                            ->placeholder('Ninguna tienda asociada')
                                            ->icon('heroicon-o-building-storefront')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->extraAttributes(['class' => 'bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl border-none mt-6']),

                        // ESPACIO PARA LA SIGUIENTE FASE
                        Group::make([]),
                    ]),
            ]);
    }
}
