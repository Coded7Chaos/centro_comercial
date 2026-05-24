<?php

namespace App\Filament\Resources\Infraestructuras\Pages;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class EditInfraestructurasCustom extends Page
{
    protected static string $resource = InfraestructurasResource::class;

    protected string $view = 'filament.resources.infraestructuras.pages.create-infraestructura-custom';

    protected static ?string $title = 'Editar infraestructura';

    public $infraId;
    public $nombre = '';
    public $ubicacion = '';
    public $lat = '';
    public $long = '';
    public $pisos = [];

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('Update:Infraestructuras') ?? false;
    }

    public function mount($record)
    {
        $infra = Infraestructuras::with(['pisosInfraestructura.tiendas.marcas'])->findOrFail($record);
        
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
                    'tamano' => $tienda->tamano,
                    'descripcion' => $tienda->descripcion,
                    'estado' => $tienda->id_estado,
                    'marcas' => $tienda->marcas->pluck('id')->toArray(),
                ];
            }

            $this->pisos[] = [
                'id' => $piso->id,
                'nombre' => $piso->nombre,
                'estado' => $piso->estado ?? 'activo',
                'tiendas' => $tiendas,
            ];
        }
    }

    public function addPiso()
    {
        $numeroPiso = count($this->pisos) + 1;
        $this->pisos[] = [
            'nombre' => "Piso $numeroPiso",
            'estado' => 'activo',
            'tiendas' => [
                [
                    'nombre' => '',
                    'numero' => '1',
                    'telefono_referencia' => '',
                    'tamano' => '',
                    'descripcion' => '',
                    'estado' => 1,
                    'marcas' => [],
                ]
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
            'nombre' => '',
            'numero' => (string)$proximoNumero,
            'telefono_referencia' => '',
            'tamano' => '',
            'descripcion' => '',
            'estado' => 1,
            'marcas' => [],
        ];
    }

    public function removeTienda($pisoIndex, $tiendaIndex)
    {
        unset($this->pisos[$pisoIndex]['tiendas'][$tiendaIndex]);
        $this->pisos[$pisoIndex]['tiendas'] = array_values($this->pisos[$pisoIndex]['tiendas']);
    }

    public function save()
    {
        $this->validate([
            'nombre' => 'required|min:3',
            'ubicacion' => 'required',
            'lat' => 'required',
            'long' => 'required',
            'pisos.*.nombre' => 'required',
            'pisos.*.estado' => 'required|in:activo,inactivo',
            'pisos.*.tiendas.*.nombre' => 'nullable',
            'pisos.*.tiendas.*.numero' => 'required',
            'pisos.*.tiendas.*.telefono_referencia' => 'nullable',
            'pisos.*.tiendas.*.tamano' => 'nullable|numeric',
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
                            'cantidad_tiendas' => count($pisoData['tiendas']),
                            'estado' => $pisoData['estado'] ?? 'activo',
                        ]
                    );

                    $pisoIdsMantener[] = $piso->id;
                    $tiendaIdsMantener = [];

                    foreach ($pisoData['tiendas'] as $tiendaData) {
                        $tienda = InfraestructurasTiendas::updateOrCreate(
                            ['id' => $tiendaData['id'] ?? null],
                            [
                                'infraestructura_piso_id' => $piso->id,
                                'nombre' => $tiendaData['nombre'],
                                'numero' => $tiendaData['numero'],
                                'telefono_referencia' => $tiendaData['telefono_referencia'],
                                'tamano' => $tiendaData['tamano'],
                                'descripcion' => $tiendaData['descripcion'],
                                'id_estado' => $tiendaData['estado'],
                            ]
                        );

                        $tiendaIdsMantener[] = $tienda->id;

                        if (isset($tiendaData['marcas'])) {
                            $tienda->marcas()->sync($tiendaData['marcas']);
                        }
                    }

                    // Borrar tiendas que ya no están en este piso
                    $piso->tiendas()->whereNotIn('id', $tiendaIdsMantener)->get()->each(function($t) {
                        $t->marcas()->detach();
                        $t->delete();
                    });
                }

                // Borrar pisos que ya no están en la infraestructura
                $infra->pisosInfraestructura()->whereNotIn('id', $pisoIdsMantener)->get()->each(function($p) {
                    $p->tiendas->each(function($t) {
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
