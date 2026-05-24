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
                            $q->where('nombres', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('ci')
                    ->label('CI')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Cuenta asociada')
                    ->searchable()
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
