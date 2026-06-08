<?php

namespace App\Filament\Resources\Marcas\Pages;

use App\Filament\Resources\Marcas\MarcasResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMarcas extends EditRecord
{
    protected static string $resource = MarcasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->modalHeading('Eliminar marca')
                ->modalDescription('¿Está seguro de que desea eliminar esta marca? Esta acción no se puede deshacer.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('comentario')
                        ->label('Comentario / Motivo de eliminación (opcional)')
                        ->placeholder('Indique al usuario el motivo por el cual se elimina la marca...'),
                ])
                ->action(function ($record, array $data) {
                    // Validaciones: no tener productos asociados
                    if (\App\Models\Productos::where('marca_id', $record->id)->exists()) {
                        Notification::make()
                            ->title('No se puede eliminar la marca')
                            ->body('Esta marca tiene productos asociados en el catálogo.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Validaciones: no estar asignada a tiendas
                    if ($record->tiendas()->exists()) {
                        Notification::make()
                            ->title('No se puede eliminar la marca')
                            ->body('Esta marca está asignada a un local comercial activo.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $clienteId = $record->cliente_id;
                    $nombreMarca = $record->nombre;

                    // Borrar logo físico del storage
                    if ($record->logo) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($record->logo);
                    }

                    // Eliminar el registro (soft delete)
                    $record->delete();

                    // Enviar notificación al cliente
                    if ($clienteId) {
                        $mensaje = 'Tu marca "' . $nombreMarca . '" ha sido eliminada por un administrador.';
                        if (!empty($data['comentario'])) {
                            $mensaje .= ' Razón: ' . $data['comentario'];
                        }

                        \App\Models\ClientNotification::create([
                            'cliente_id' => $clienteId,
                            'tipo' => 'danger',
                            'titulo' => 'Un administrador ha eliminado tu marca',
                            'mensaje' => $mensaje,
                            'leido' => false,
                        ]);
                    }

                    Notification::make()
                        ->title('Marca eliminada')
                        ->success()
                        ->send();

                    $this->redirect($this->getRedirectUrl());
                }),
        ];
    }
}
