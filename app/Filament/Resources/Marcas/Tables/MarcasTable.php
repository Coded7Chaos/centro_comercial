<?php

namespace App\Filament\Resources\Marcas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarcasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Marca ID')
                    ->sortable(),
                \Filament\Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('cliente_id')
                    ->label('Cliente ID')
                    ->sortable()
                    ->searchable()
                    ->default('-'),
                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->default('Global')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('cliente.user', function ($q) use ($search) {
                            $q->where('nombres', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%");
                        });
                    })
                    ->limit(20)
                    ->tooltip(fn($record) => $record->cliente?->nombre_completo ?? 'Marca Global')
                    ->width('220px'),
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray',
                    })
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
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
