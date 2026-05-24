<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductosResource;
use App\Models\ProductosImagenes;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProductos extends EditRecord
{
    protected static string $resource = ProductosResource::class;

    protected array $imagenesTemporales = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['imagenes'] = $this->record
            ->imagenes
            ->map(function ($imagen) {

                return [
                    'url' => $imagen->url,
                    'tipo' => $imagen->tipo,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
                ->title('Error al guardar cambios')
                ->body('Debe existir una imagen principal.')
                ->danger()
                ->send();

            $this->halt();
        }

        if ($principales > 1) {

            Notification::make()
                ->title('Error al guardar cambios')
                ->body('Solo puede existir una imagen principal.')
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

        $this->imagenesTemporales = $imagenes->toArray();

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

        foreach ($this->imagenesTemporales as $imagen) {

            ProductosImagenes::create([
                'producto_id' => $this->record->id,
                'url' => $imagen['url'],
                'tipo' => $imagen['tipo'],
            ]);
        }
    }
}