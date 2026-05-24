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
                    ->searchable(),
                TextColumn::make('apellido_paterno')
                    ->placeholder('NA'),
                TextColumn::make('apellido_materno')
                    ->placeholder('NA'),
                TextColumn::make('email')
                    ->searchable(),
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
