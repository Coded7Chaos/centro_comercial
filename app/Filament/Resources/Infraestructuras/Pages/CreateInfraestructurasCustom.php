<?php

namespace App\Filament\Resources\Infraestructuras\Pages;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class CreateInfraestructurasCustom extends Page
{
    protected static string $resource = InfraestructurasResource::class;

    protected string $view = 'filament.resources.infraestructuras.pages.create-infraestructura-custom';

    protected static ?string $title = 'Crear nueva infraestructura';

    // Propiedades del formulario
    public $nombre = '';
    public $ubicacion = '';
    public $lat = '-16.5000'; // Coordenadas por defecto (Bolivia/La Paz ej)
    public $long = '-68.1500';
    public $pisos = [];

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('Create:Infraestructuras') ?? false;
    }

    public function mount()
    {
        // Inicializar con un piso vacío
        $this->addPiso();
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
        
        // No renombrar automáticamente para permitir nombres personalizados
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
                // 1. Crear Infraestructura
                $infra = Infraestructuras::create([
                    'nombre' => $this->nombre,
                    'ubicacion' => $this->ubicacion,
                    'lat' => $this->lat,
                    'long' => $this->long,
                    'pisos' => count($this->pisos),
                ]);

                // 2. Crear Pisos y Tiendas
                foreach ($this->pisos as $pisoData) {
                    $piso = InfraestructurasPisos::create([
                        'infraestructura_id' => $infra->id,
                        'nombre' => $pisoData['nombre'],
                        'cantidad_tiendas' => count($pisoData['tiendas']),
                        'estado' => $pisoData['estado'] ?? 'activo',
                    ]);

                    foreach ($pisoData['tiendas'] as $tiendaData) {
                        $tienda = InfraestructurasTiendas::create([
                            'infraestructura_piso_id' => $piso->id,
                            'nombre' => $tiendaData['nombre'],
                            'numero' => $tiendaData['numero'],
                            'telefono_referencia' => $tiendaData['telefono_referencia'],
                            'tamano' => $tiendaData['tamano'],
                            'descripcion' => $tiendaData['descripcion'],
                            'id_estado' => $tiendaData['estado'],
                        ]);

                        if (!empty($tiendaData['marcas'])) {
                            $tienda->marcas()->sync($tiendaData['marcas']);
                        }
                    }
                }
            });

            Notification::make()
                ->title('Infraestructura creada exitosamente')
                ->success()
                ->send();

            return redirect()->to(InfraestructurasResource::getUrl('index'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
