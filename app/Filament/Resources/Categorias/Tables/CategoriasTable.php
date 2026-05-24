<?php

namespace App\Filament\Resources\Categorias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // NOMBRE
                TextColumn::make('nombre')
                    ->searchable(),

                // TIPO (CATEGORÍA / SUBCATEGORÍA)
                TextColumn::make('tipo')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'categoria', 'padre' => 'success',
                        'subcategoria', 'hijo' => 'info',
                        default => 'gray',
                    }),

                // CATEGORÍA PADRE (RELACIÓN)
                TextColumn::make('padre.nombre')
                    ->label('Categoría padre')
                    ->placeholder('—'),

                // ESTADO
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                    }),

                // FECHAS
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
                SelectFilter::make('tipo')
                    ->label('Ver por tipo')
                    ->options([
                        'categoria' => 'Solo categorías',
                        'subcategoria' => 'Solo subcategorías',
                    ])
                    ->placeholder('Todas (categorías + subcategorías)'),
            ])
            ->recordActions([
                ViewAction::make()->color('info'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
