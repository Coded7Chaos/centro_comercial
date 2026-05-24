<?php

namespace App\Filament\Resources\SuscripcionesTarifas\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\SuscripcionesTarifas;

class SuscripcionesTarifasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('etiqueta')
                    ->label('Etiqueta')
                    ->badge()
                    ->placeholder('—')
                    ->color('gray'),
                TextColumn::make('tamano_min')
                    ->label('Desde (m²)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('tamano_max')
                    ->label('Hasta (m²)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('BOB')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options(SuscripcionesTarifas::tipos()),
            ])
            ->defaultSort('tamano_min')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
