<?php

namespace App\Filament\Resources\Marcas\Schemas;

use App\Models\Marcas;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MarcasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('logo')
                    ->label('Logo')
                    ->getStateUsing(
                        fn($record) =>
                        $record->logo
                            ? asset('storage/' . $record->logo)
                            : 'https://placehold.co/120x120?text=Sin+Logo'
                    )
                    ->circular()
                    ->size(120)
                    ->extraImgAttributes([
                        'class' => 'object-cover border'
                    ]),
                TextEntry::make('id')
                    ->label('Marca ID'),
                TextEntry::make('nombre'),
                TextEntry::make('cliente.id')
                    ->label('Cliente ID'),
                TextEntry::make('cliente.nombre_completo')
                    ->label('Cliente'),
                TextEntry::make('descripcion')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('estado'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn(Marcas $record): bool => $record->trashed()),
            ]);
    }
}
