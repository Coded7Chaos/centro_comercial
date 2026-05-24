<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CategoriasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // NOMBRE
                TextEntry::make('nombre')
                    ->label('Nombre'),

                // DESCRIPCIÓN
                TextEntry::make('descripcion')
                    ->label('Descripción')
                    ->placeholder('-')
                    ->columnSpanFull(),

                // TIPO
                TextEntry::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'categoria' => 'success',
                        'subcategoria' => 'info',
                        default => 'gray',
                    }),

                // 🔥 CATEGORÍA PADRE (RELACIÓN)
                TextEntry::make('padre.nombre')
                    ->label('Categoría padre')
                    ->placeholder('Categoría principal')
                    ->visible(fn ($record) => $record->tipo === 'subcategoria'),

                // ESTADO
                TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray',
                    }),

                // FECHA CREACIÓN
                TextEntry::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->placeholder('-'),

                // FECHA ACTUALIZACIÓN
                TextEntry::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}