<?php

namespace App\Filament\Resources\Infraestructuras\Pages;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class EditInfraestructurasCustom extends Page
{
    use WithFileUploads;

    protected static string $resource = InfraestructurasResource::class;

    protected string $view = 'filament.resources.infraestructuras.pages.create-infraestructura-custom';

    protected static ?string $title = 'Editar infraestructura';

    public $infraId;

    public $nombre = '';

    public $ubicacion = '';

    public $lat = '';

    public $long = '';

    public $pisos = [];

    public $backgroundModalOpen = false;

    public $backgroundModalPisoIndex = null;

    public $backgroundPreview = '';

    public $backgroundUpload = null;

    public bool $backgroundSelectionMade = false;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('Update:Infraestructuras') ?? false;
    }

    public function mount($record)
    {
        $infra = Infraestructuras::with(['pisosInfraestructura.tiendas' => fn ($query) => $query->orderBy('numero')])->findOrFail($record);

        $this->infraId = $infra->id;
        $this->nombre = $infra->nombre;
        $this->ubicacion = $infra->ubicacion;
        $this->lat = $infra->lat;
        $this->long = $infra->long;

        foreach ($infra->pisosInfraestructura as $piso) {
            $tiendas = [];
            foreach ($piso->tiendas as $tienda) {
                $tiendas[] = [
                    'id' => $tienda->id,
                    'nombre' => $tienda->nombre,
                    'numero' => $tienda->numero,
                    'telefono_referencia' => $tienda->telefono_referencia,
                    'email_contacto' => $tienda->email_contacto,
                    'tamano' => $tienda->tamano,
                    'descripcion' => $tienda->descripcion,
                    'estado' => $tienda->id_estado,
                ];
            }

            $this->pisos[] = [
                'id' => $piso->id,
                'nombre' => $piso->nombre,
                'numero' => $piso->numero ?: $piso->nombre,
                'estado' => $piso->estado ?? 'activo',
                'imagen_fondo' => $piso->imagen_fondo ?? '/images/backgrounds/bg_mall_white.jpg',
                'tiendas' => $tiendas,
            ];
        }
    }

    protected function defaultStorePhone(): string
    {
        return auth()->user()?->cliente?->numero_celular
            ? (string) auth()->user()->cliente->numero_celular
            : '+591 7000 0000';
    }

    protected function defaultStoreEmail(): ?string
    {
        return auth()->user()?->email;
    }

    public function addPiso()
    {
        $numeroPiso = count($this->pisos) + 1;
        $nivelDefault = $numeroPiso === 1 ? 'Planta baja' : 'Piso '.($numeroPiso - 1);
        $this->pisos[] = [
            'nombre' => "Piso $numeroPiso",
            'numero' => $nivelDefault,
            'estado' => 'activo',
            'imagen_fondo' => '/images/backgrounds/bg_mall_white.jpg',
            'tiendas' => [
                [
                    'numero' => '1',
                    'telefono_referencia' => $this->defaultStorePhone(),
                    'email_contacto' => $this->defaultStoreEmail(),
                    'tamano' => '20',
                    'descripcion' => '',
                    'estado' => 1,
                ],
            ],
        ];
    }

    public function removePiso($index)
    {
        unset($this->pisos[$index]);
        $this->pisos = array_values($this->pisos);
    }

    public function addTienda($pisoIndex)
    {
        $proximoNumero = count($this->pisos[$pisoIndex]['tiendas']) + 1;
        $this->pisos[$pisoIndex]['tiendas'][] = [
            'numero' => (string) $proximoNumero,
            'telefono_referencia' => $this->defaultStorePhone(),
            'email_contacto' => $this->defaultStoreEmail(),
            'tamano' => '20',
            'descripcion' => '',
            'estado' => 1,
        ];
    }

    public function removeTienda($pisoIndex, $tiendaIndex)
    {
        unset($this->pisos[$pisoIndex]['tiendas'][$tiendaIndex]);
        $this->pisos[$pisoIndex]['tiendas'] = array_values($this->pisos[$pisoIndex]['tiendas']);
    }

    public function getBackgroundOptionsProperty(): array
    {
        return [
            ['label' => 'Blanco', 'url' => '/images/backgrounds/bg_mall_white.jpg'],
            ['label' => 'Calido', 'url' => '/images/backgrounds/bg_mall_warm.jpg'],
            ['label' => 'Gris', 'url' => '/images/backgrounds/bg_mall_grey.jpg'],
            ['label' => 'Oscuro', 'url' => '/images/backgrounds/bg_mall_dark.jpg'],
        ];
    }

    public function getBackgroundModalPreviewUrlProperty(): string
    {
        if ($this->backgroundUpload) {
            try {
                return $this->backgroundUpload->temporaryUrl();
            } catch (\Throwable $exception) {
                return $this->backgroundPreview ?: '/images/backgrounds/bg_mall_white.jpg';
            }
        }

        return $this->backgroundPreview ?: '/images/backgrounds/bg_mall_white.jpg';
    }

    public function openBackgroundModal($pisoIndex): void
    {
        if (! isset($this->pisos[$pisoIndex])) {
            return;
        }

        $this->backgroundModalPisoIndex = $pisoIndex;
        $this->backgroundPreview = $this->pisos[$pisoIndex]['imagen_fondo'] ?? '/images/backgrounds/bg_mall_white.jpg';
        $this->backgroundModalOpen = true;
        $this->backgroundUpload = null;
        $this->backgroundSelectionMade = false;
        $this->resetErrorBag('backgroundUpload');
        $this->resetErrorBag('backgroundSelection');
    }

    public function selectBackground(string $url): void
    {
        $this->backgroundPreview = $url;
        $this->backgroundUpload = null;
        $this->backgroundSelectionMade = true;
        $this->resetErrorBag('backgroundUpload');
        $this->resetErrorBag('backgroundSelection');
    }

    public function updatedBackgroundUpload(): void
    {
        $this->validateOnly('backgroundUpload', [
            'backgroundUpload' => 'file|mimetypes:image/jpeg,image/png,image/webp,image/gif,image/avif|max:5120',
        ]);

        $this->backgroundSelectionMade = true;
        $this->resetErrorBag('backgroundSelection');
    }

    public function confirmBackgroundImage(): void
    {
        if ($this->backgroundModalPisoIndex === null || ! isset($this->pisos[$this->backgroundModalPisoIndex])) {
            return;
        }

        if (! $this->backgroundSelectionMade && ! $this->backgroundUpload) {
            $this->addError('backgroundSelection', 'Debes elegir una imagen de fondo antes de confirmar.');

            return;
        }

        $selectedBackground = $this->backgroundPreview ?: '/images/backgrounds/bg_mall_white.jpg';

        if ($this->backgroundUpload) {
            $this->validate([
                'backgroundUpload' => 'file|mimetypes:image/jpeg,image/png,image/webp,image/gif,image/avif|max:5120',
            ]);

            $path = $this->backgroundUpload->storeAs(
                'infraestructuras/fondos',
                Str::uuid().'.'.$this->backgroundUploadExtension(),
                'public',
            );

            $selectedBackground = Storage::url($path);
        }

        $this->pisos[$this->backgroundModalPisoIndex]['imagen_fondo'] = $selectedBackground;

        $pisoId = $this->pisos[$this->backgroundModalPisoIndex]['id'] ?? null;

        if ($pisoId) {
            InfraestructurasPisos::whereKey($pisoId)->update([
                'imagen_fondo' => $selectedBackground,
            ]);

            Notification::make()
                ->title('Imagen de fondo actualizada')
                ->success()
                ->send();
        }

        $this->closeBackgroundModal();
    }

    public function closeBackgroundModal(): void
    {
        $this->backgroundModalOpen = false;
        $this->backgroundModalPisoIndex = null;
        $this->backgroundPreview = '';
        $this->backgroundUpload = null;
        $this->backgroundSelectionMade = false;
        $this->resetErrorBag('backgroundUpload');
        $this->resetErrorBag('backgroundSelection');
    }

    protected function backgroundUploadExtension(): string
    {
        return match ($this->backgroundUpload?->getMimeType()) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            default => 'jpg',
        };
    }

    public function save()
    {
        $this->validate([
            'nombre' => 'required|min:3',
            'ubicacion' => 'required',
            'lat' => 'required',
            'long' => 'required',
            'pisos.*.nombre' => 'required',
            'pisos.*.numero' => 'required',
            'pisos.*.estado' => 'required|in:activo,inactivo',
            'pisos.*.imagen_fondo' => 'required|string',
            'pisos.*.tiendas.*.numero' => 'required',
            'pisos.*.tiendas.*.telefono_referencia' => 'required|string|max:30',
            'pisos.*.tiendas.*.email_contacto' => 'nullable|email|max:255',
            'pisos.*.tiendas.*.tamano' => 'required|numeric|min:0.01',
            'pisos.*.tiendas.*.descripcion' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () {
                $infra = Infraestructuras::findOrFail($this->infraId);
                $infra->update([
                    'nombre' => $this->nombre,
                    'ubicacion' => $this->ubicacion,
                    'lat' => $this->lat,
                    'long' => $this->long,
                    'pisos' => count($this->pisos),
                ]);

                $pisoIdsMantener = [];

                foreach ($this->pisos as $pisoData) {
                    $piso = InfraestructurasPisos::updateOrCreate(
                        ['id' => $pisoData['id'] ?? null],
                        [
                            'infraestructura_id' => $infra->id,
                            'nombre' => $pisoData['nombre'],
                            'numero' => $pisoData['numero'],
                            'cantidad_tiendas' => count($pisoData['tiendas']),
                            'estado' => $pisoData['estado'] ?? 'activo',
                            'imagen_fondo' => $pisoData['imagen_fondo'] ?? '/images/backgrounds/bg_mall_white.jpg',
                        ]
                    );

                    $pisoIdsMantener[] = $piso->id;
                    $tiendaIdsMantener = [];

                    foreach ($pisoData['tiendas'] as $tiendaData) {
                        $tienda = InfraestructurasTiendas::updateOrCreate(
                            ['id' => $tiendaData['id'] ?? null],
                            [
                                'infraestructura_piso_id' => $piso->id,
                                'numero' => $tiendaData['numero'],
                                'telefono_referencia' => $tiendaData['telefono_referencia'],
                                'email_contacto' => $tiendaData['email_contacto'] ?? null,
                                'tamano' => $tiendaData['tamano'],
                                'descripcion' => $tiendaData['descripcion'] ?? null,
                                'id_estado' => $tiendaData['estado'],
                            ]
                        );

                        $tiendaIdsMantener[] = $tienda->id;
                    }

                    // Borrar tiendas que ya no están en este piso
                    $piso->tiendas()->whereNotIn('id', $tiendaIdsMantener)->get()->each(function ($t) {
                        $t->marcas()->detach();
                        $t->delete();
                    });
                }

                // Borrar pisos que ya no están en la infraestructura
                $infra->pisosInfraestructura()->whereNotIn('id', $pisoIdsMantener)->get()->each(function ($p) {
                    $p->tiendas->each(function ($t) {
                        $t->marcas()->detach();
                        $t->delete();
                    });
                    $p->delete();
                });
            });

            Notification::make()
                ->title('Infraestructura actualizada correctamente')
                ->success()
                ->send();

            return redirect()->to(InfraestructurasResource::getUrl('index'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al actualizar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
