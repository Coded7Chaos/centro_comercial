<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;

class ProductosInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // IMAGEN PRINCIPAL

                ImageEntry::make('imagen_principal')
                    ->label('Imagen principal')
                    ->getStateUsing(
                        fn($record) =>
                        optional(
                            $record->imagenes
                                ->where('tipo', 'principal')
                                ->first()
                        )?->url
                            ? asset('storage/' . $record->imagenes
                                ->where('tipo', 'principal')
                                ->first()->url)
                            : null
                    )
                    ->height(260)
                    ->width(260),



                TextEntry::make('id')
                    ->label('Producto ID'),

                TextEntry::make('nombre')
                    ->label('Nombre'),

                TextEntry::make('precio')
                    ->label('Precio')
                    ->money('BOB')
                    ->badge()
                    ->color('success'),

                TextEntry::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray',
                    }),

                TextEntry::make('categoria.nombre')
                    ->label('Categoría')
                    ->placeholder('-'),

                TextEntry::make('subcategoria.nombre')
                    ->label('Subcategoría')
                    ->placeholder('-'),

                TextEntry::make('marca.nombre')
                    ->label('Marca')
                    ->placeholder('-'),



                TextEntry::make('descripcion')
                    ->label('Descripción')
                    ->placeholder('Sin descripción')
                    ->columnSpanFull(),

                // GALERÍA

                TextEntry::make('galeria_label')
                    ->label('')
                    ->state('Galería')
                    ->weight('bold')
                    ->columnSpanFull(),

                TextEntry::make('galeria_tipo')
                    ->label('')
                    ->state('Tipo de imágenes: secundaria')
                    ->color('gray')
                    ->columnSpanFull(),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema(

                        $schema->getRecord()
                            ->imagenes
                            ->where('tipo', 'secundaria')
                            ->map(function ($imagen) {

                                return Grid::make(1)
                                    ->schema([

                                        ImageEntry::make('url')

                                            ->state(
                                                asset('storage/' . $imagen->url)
                                            )

                                            ->height(180)
                                            ->width(180),

                                    ]);
                            })->toArray()

                    ),

                TextEntry::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
