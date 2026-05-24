<?php

namespace App\Filament\Resources\Infraestructuras\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class InfraestructurasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                TextEntry::make('pisos')
                    ->numeric(),
                TextEntry::make('tiendas_activas')
                    ->label('Tiendas activas')
                    ->state(
                        fn($record) =>
                        $record->pisosInfraestructura
                            ->flatMap->tiendas
                            ->where('estado', 'activo')
                            ->count()
                    ),

                TextEntry::make('tiendas_disponibles')
                    ->label('Tiendas disponibles')
                    ->state(
                        fn($record) =>
                        $record->pisosInfraestructura
                            ->flatMap->tiendas
                            ->where('estado', 'disponible')
                            ->count()
                    ),

                TextEntry::make('tiendas_inactivas')
                    ->label('Tiendas inactivas')
                    ->state(
                        fn($record) =>
                        $record->pisosInfraestructura
                            ->flatMap->tiendas
                            ->where('estado', 'inactivo')
                            ->count()
                    ),
                TextEntry::make('total_tiendas')
                    ->label('Total de tiendas')
                    ->state(
                        fn($record) =>
                        $record->pisosInfraestructura
                            ->sum('cantidad_tiendas')
                    )
                    ->badge()
                    ->color('success'),
                TextEntry::make('ubicacion'),
                ViewEntry::make('mapa')
                    ->view('filament.forms.components.leaflet-map')
                    ->columnSpanFull(),
                TextEntry::make('lat')
                    ->placeholder('-'),
                TextEntry::make('long')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
