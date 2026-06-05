<?php

namespace App\Filament\Resources\Productos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class ProductosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagen_principal')
                    ->label('Imagen')
                    ->getStateUsing(function ($record) {

                        $imagen = $record->imagenes
                            ->where('tipo', 'principal')
                            ->first();

                        if (!$imagen) {
                            return null;
                        }

                        return str_starts_with($imagen->url, 'http')
                            ? $imagen->url
                            : asset('storage/' . $imagen->url);
                    })
                    ->square()
                    ->size(60),
                TextColumn::make('nombre')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                    }),
                TextColumn::make('precio')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('categoria_id')
                    ->label('Categoría')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $state . ' - ' . ($record->categoria->nombre ?? '')
                    )
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('categoria', function ($q) use ($search) {
                            $q->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                        });
                    }),

                TextColumn::make('marca_id')
                    ->label('Marca')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $state . ' - ' . ($record->marca->nombre ?? '')
                    )
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('marca', function ($q) use ($search) {
                            $q->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                        });
                    }),
                TextColumn::make('estado')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereRaw("unaccent(lower(estado)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
