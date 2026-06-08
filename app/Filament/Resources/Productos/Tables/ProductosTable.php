<?php

namespace App\Filament\Resources\Productos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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

                        if (! $imagen) {
                            return null;
                        }

                        return str_starts_with($imagen->url, 'http')
                            ? $imagen->url
                            : asset('storage/'.$imagen->url);
                    })
                    ->square()
                    ->size(60),
                TextColumn::make('nombre')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereRaw('unaccent(lower(nombre)) ILIKE unaccent(lower(?))', ["%{$search}%"]);
                    }),
                TextColumn::make('precio')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('categoria_id')
                    ->label('Categoría')
                    ->formatStateUsing(
                        fn ($state, $record) => $state.' - '.($record->categoria->nombre ?? '')
                    )
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('categoria', function ($q) use ($search) {
                            $q->whereRaw('unaccent(lower(nombre)) ILIKE unaccent(lower(?))', ["%{$search}%"]);
                        });
                    }),

                TextColumn::make('marca_id')
                    ->label('Marca')
                    ->formatStateUsing(
                        fn ($state, $record) => $state.' - '.($record->marca->nombre ?? '')
                    )
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('marca', function ($q) use ($search) {
                            $q->whereRaw('unaccent(lower(nombre)) ILIKE unaccent(lower(?))', ["%{$search}%"]);
                        });
                    }),
                TextColumn::make('tienda.nombre')
                    ->label('Tienda')
                    ->getStateUsing(fn ($record) => $record->tienda?->nombre ?: ($record->tienda ? "Tienda #{$record->tienda->numero}" : 'Sin tienda'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('tienda', function ($q) use ($search) {
                            $q->whereRaw('unaccent(lower(nombre)) ILIKE unaccent(lower(?))', ["%{$search}%"])
                                ->orWhereRaw('unaccent(lower(numero)) ILIKE unaccent(lower(?))', ["%{$search}%"]);
                        });
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
                DeleteAction::make()
                    ->modalHeading('Eliminar producto')
                    ->modalDescription('¿Está seguro de que desea eliminar este producto? Esta acción no se puede deshacer.')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comentario')
                            ->label('Comentario / Motivo de eliminación (opcional)')
                            ->placeholder('Indique al usuario el motivo por el cual se elimina el producto...'),
                    ])
                    ->action(function ($record, array $data) {
                        $tienda = $record->tienda;
                        $clienteId = $tienda?->cliente_id;
                        $nombreProducto = $record->nombre;

                        // Borrar imágenes físicas del storage
                        foreach ($record->imagenes as $img) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($img->url);
                        }

                        // Eliminar el registro
                        $record->delete();

                        // Enviar notificación al cliente
                        if ($clienteId) {
                            $mensaje = 'Tu producto "' . $nombreProducto . '" ha sido eliminado por un administrador.';
                            if (!empty($data['comentario'])) {
                                $mensaje .= ' Razón: ' . $data['comentario'];
                            }

                            \App\Models\ClientNotification::create([
                                'cliente_id' => $clienteId,
                                'tipo' => 'danger',
                                'titulo' => 'Un administrador ha eliminado tu producto',
                                'mensaje' => $mensaje,
                                'leido' => false,
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Producto eliminado')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
