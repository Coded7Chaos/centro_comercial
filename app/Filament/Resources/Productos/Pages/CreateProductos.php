<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductosResource;
use App\Models\ProductosImagenes;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProductos extends CreateRecord
{
    protected static string $resource = ProductosResource::class;

    protected array $imagenesTemporales = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $imagenes = collect($data['imagenes'] ?? []);

        // =========================
        // VALIDACIONES
        // =========================

        if ($imagenes->isEmpty()) {

            Notification::make()
                ->title('Error al crear producto')
                ->body('Debe existir al menos una imagen.')
                ->danger()
                ->send();

            $this->halt();
        }

        if ($imagenes->count() > 6) {

            Notification::make()
                ->title('Error al crear producto')
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

    protected function afterCreate(): void
    {
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
