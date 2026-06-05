<?php

namespace App\Filament\Resources\Usuarios\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsuariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombres')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(function ($q) use ($search) {
                            $q->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                              ->orWhereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                              ->orWhereRaw("unaccent(lower(apellido_materno)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                        });
                    }),
                TextColumn::make('apellido_paterno')
                    ->placeholder('NA'),
                TextColumn::make('apellido_materno')
                    ->placeholder('NA'),
                TextColumn::make('email')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereRaw("unaccent(lower(email)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                    }),
                TextColumn::make('roles.name')
                    ->placeholder('NA')
                    ->label('Roles')
                    ->badge()
                    ->separator(','),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
