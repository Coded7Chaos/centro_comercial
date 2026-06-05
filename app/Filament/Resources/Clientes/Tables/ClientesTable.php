<?php

namespace App\Filament\Resources\Clientes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('user.nombres')
                    ->label('Cliente')
                    ->state(fn($record) => $record->user 
                        ? "{$record->user->nombres} {$record->user->apellido_paterno} {$record->user->apellido_materno}"
                        : 'Sin cuenta vinculada')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_materno)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
                        });
                    }),

                TextColumn::make('ci')
                    ->label('CI')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereRaw("unaccent(lower(ci)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                    }),

                TextColumn::make('user.email')
                    ->label('Cuenta asociada')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->whereRaw("unaccent(lower(email)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                        });
                    })
                    ->placeholder('Sin cuenta'),

                TextColumn::make('numero_celular')
                    ->label('Celular')

                    ->formatStateUsing(
                        fn($record) => ($record->codigo_pais ?? '+591') . ' ' .
                            ($record->numero_celular ?? '')
                    )

                    ->placeholder('Sin número'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
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
