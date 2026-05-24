<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductosResource;
use App\Models\ProductosImagenes;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProductos extends CreateRecord
{
    protected static string $resource = ProductosResource::class;

    protected array $imagenesTemporales = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $imagenes = collect($data['imagenes'] ?? []);

        $principales = $imagenes
            ->where('tipo', 'principal')
            ->count();

        // =========================
        // VALIDACIONES
        // =========================

        if ($principales < 1) {

            Notification::make()
                ->title('Error al crear producto')
                ->body('Debe existir una imagen principal.')
                ->danger()
                ->send();

            $this->halt();
        }

        if ($principales > 1) {

            Notification::make()
                ->title('Error al crear producto')
                ->body('Solo puede existir una imagen principal.')
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

        $this->imagenesTemporales = $imagenes->toArray();

        unset($data['imagenes']);

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->imagenesTemporales as $imagen) {

            ProductosImagenes::create([
                'producto_id' => $this->record->id,
                'url' => $imagen['url'],
                'tipo' => $imagen['tipo'],
            ]);
        }
    }
}