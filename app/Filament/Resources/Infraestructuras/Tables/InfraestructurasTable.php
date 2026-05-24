<?php

namespace App\Filament\Resources\Infraestructuras\Tables;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InfraestructurasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('ubicacion')
                    ->searchable()
                    ->description(function ($record) {
                        $pisos = $record->pisos ?? 0;
                        $totalTiendas = $record->pisosInfraestructura->flatMap->tiendas->count();
                        
                        return "{$pisos} pisos      -       {$totalTiendas} tiendas en total";
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
                Action::make('diseno')
                    ->label('Diseño')
                    ->icon('heroicon-o-building-office')
                    ->color('success')
                    ->url(
                        fn($record) =>
                        InfraestructurasResource::getUrl(
                            'diseno',
                            ['record' => $record]
                        )
                    ),
                Action::make('tiendas')
                    ->label('Tiendas')
                    ->icon('heroicon-o-building-storefront')
                    ->color('warning')

                    ->form([

                        Select::make('piso_id')
                            ->label('Seleccionar piso')
                            ->options(

                                fn($record) =>

                                $record
                                    ->pisosInfraestructura()
                                    ->orderBy('id')
                                    ->pluck('nombre', 'id')

                            )
                            ->required()
                            ->searchable()
                            ->preload(),

                    ])

                    ->action(function ($record, array $data) {

                        return redirect(

                            InfraestructurasResource::getUrl(
                                'tiendas-propietarios',
                                [
                                    'record' => $record,
                                    'piso' => $data['piso_id'],
                                ]
                            )

                        );
                    }),
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
