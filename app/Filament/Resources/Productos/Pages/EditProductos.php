<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductosResource;
use App\Models\Categorias;
use App\Models\ProductosImagenes;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProductos extends EditRecord
{
    protected static string $resource = ProductosResource::class;

    protected array $imagenesTemporales = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
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

                    Notification::make()
                        ->title('Producto eliminado')
                        ->success()
                        ->send();

                    $this->redirect($this->getRedirectUrl());
                }),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return ProductosResource::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['cliente_temp'] = $this->record->tienda?->cliente_id;

        $subcategoriaId = $this->record->subcategoria_id ?: $this->record->categoria_id;
        $subcategoria = $subcategoriaId ? Categorias::find($subcategoriaId) : null;

        $data['categoria_id'] = $subcategoria?->categoria_padre_id ?: $this->record->categoria_id;
        $data['subcategoria_id'] = $subcategoriaId;

        $data['imagenes'] = $this->record
            ->imagenes
            ->map(function ($imagen) {

                return [
                    'url' => $imagen->url,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $imagenes = collect($data['imagenes'] ?? []);

        // =========================
        // VALIDACIONES
        // =========================

        if ($imagenes->isEmpty()) {

            Notification::make()
                ->title('Error al guardar cambios')
                ->body('Debe existir al menos una imagen.')
                ->danger()
                ->send();

            $this->halt();
        }

        if ($imagenes->count() > 6) {

            Notification::make()
                ->title('Error al guardar cambios')
                ->body('Máximo 6 imágenes permitidas.')
                ->danger()
                ->send();

            $this->halt();
        }

        // =========================
        // GUARDAR TEMPORALMENTE
        // =========================

        if (! empty($data['subcategoria_id'])) {
            $data['categoria_id'] = $data['subcategoria_id'];
        }

        $this->imagenesTemporales = $imagenes->toArray();

        unset($data['subcategoria_id']);
        unset($data['imagenes']);

        return $data;
    }

    protected function afterSave(): void
    {
        // =========================
        // BORRAR ANTERIORES
        // =========================

        $this->record->imagenes()->delete();

        // =========================
        // CREAR NUEVAS
        // =========================

        foreach ($this->imagenesTemporales as $index => $imagen) {
            $url = $this->normalizeImagePath($imagen['url'] ?? null);

            ProductosImagenes::create([
                'producto_id' => $this->record->id,
                'url' => $url,
                'tipo' => $index === 0 ? 'principal' : 'otro',
            ]);
        }
    }

    private function normalizeImagePath(mixed $state): ?string
    {
        if (is_array($state)) {
            return reset($state) ?: null;
        }

        return $state;
    }
}
