<?php

namespace App\Filament\Resources\Infraestructuras\Pages;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use App\Models\Clientes;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class TiendasPropietarios extends Page
{
    protected static string $resource =
    InfraestructurasResource::class;

    protected string $view =
    'filament.resources.infraestructuras.pages.tiendas-propietarios';

    public Infraestructuras $record;

    public ?InfraestructurasPisos $piso = null;

    public array $tiendas = [];

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('ViewAny:InfraestructurasTiendas') ?? false;
    }

    public function mount(
        Infraestructuras $record
    ): void {

        $this->record = $record;

        $pisoId = request()->get('piso');

        abort_unless($pisoId, 404);

        $this->piso =
            InfraestructurasPisos::findOrFail($pisoId);

        $cantidadActual =
            $this->piso->tiendas()->count();

        $cantidadNueva =
            $this->piso->cantidad_tiendas;

        /*
        |--------------------------------------------------------------------------
        | CREAR TIENDAS FALTANTES
        |--------------------------------------------------------------------------
        */

        if ($cantidadNueva > $cantidadActual) {

            for (
                $i = $cantidadActual + 1;
                $i <= $cantidadNueva;
                $i++
            ) {

                InfraestructurasTiendas::create([

                    'infraestructura_piso_id' =>
                    $this->piso->id,

                    'numero' => $i,

                    'nombre' => null,

                    'estado' => 'disponible',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ELIMINAR SOBRANTES
        |--------------------------------------------------------------------------
        */ elseif ($cantidadNueva < $cantidadActual) {

            $sobrantes =
                $this->piso
                ->tiendas()
                ->orderByDesc('numero')
                ->take($cantidadActual - $cantidadNueva)
                ->get();

            foreach ($sobrantes as $tienda) {

                if (
                    !$tienda->cliente_id &&
                    !$tienda->marca_id
                ) {

                    $tienda->delete();
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CARGAR TIENDAS
        |--------------------------------------------------------------------------
        */

        $this->tiendas =
            $this->piso
            ->tiendas()
            ->get()
            ->map(function ($tienda) {

                return [

                    'id' => $tienda->id,

                    'numero' => $tienda->numero,

                    'nombre' => $tienda->nombre,

                    'descripcion' => $tienda->descripcion,

                    'telefono_referencia' =>
                    $tienda->telefono_referencia,

                    'cliente_id' =>
                    $tienda->cliente_id,

                    'marca_id' =>
                    $tienda->marca_id,

                    'estado' =>
                    $tienda->estado,

                    'tamano' =>
                    $tienda->tamano,
                ];
            })
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | GUARDAR
    |--------------------------------------------------------------------------
    */

    public function guardar(): void
    {
        foreach ($this->tiendas as $tienda) {

            $tieneDatos =
                !empty($tienda['cliente_id']) ||
                !empty($tienda['marca_id']);

            /*
            |--------------------------------------------------------------------------
            | TIENDA VACÍA
            |--------------------------------------------------------------------------
            */

            if (!$tieneDatos) {

                InfraestructurasTiendas::where(
                    'id',
                    $tienda['id']
                )->update([

                    'nombre' => null,

                    'cliente_id' => null,

                    'marca_id' => null,

                    'estado' => 'disponible',
                ]);

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | GUARDAR TIENDA
            |--------------------------------------------------------------------------
            */

            if (empty($tienda['tamano'])) {

                Notification::make()
                    ->title(
                        'Debe seleccionar un tamaño'
                    )
                    ->danger()
                    ->send();

                return;
            }

            InfraestructurasTiendas::where(
                'id',
                $tienda['id']
            )->update([

                'nombre' =>
                $tienda['nombre'] ?: null,

                'descripcion' =>
                $tienda['descripcion'] ?: null,

                'telefono_referencia' =>
                $tienda['telefono_referencia'] ?: null,

                'cliente_id' =>
                $tienda['cliente_id'] ?: null,

                'marca_id' =>
                $tienda['marca_id'] ?: null,

                'estado' =>
                $tienda['estado'],

                'tamano' =>
                $tienda['tamano'] ?: 'pequeño',
            ]);
        }

        Notification::make()
            ->title(
                'Tiendas actualizadas correctamente'
            )
            ->success()
            ->send();
    }

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */

    public function getClientesProperty()
    {
        return Clientes::with('user')->get()
            ->mapWithKeys(function ($cliente) {
                $u = $cliente->user;
                $nombre = $u
                    ? trim(($u->nombres ?? '') . ' ' . ($u->apellido_paterno ?? ''))
                    : ('Cliente #' . $cliente->id);
                return [$cliente->id => $nombre ?: ('Cliente #' . $cliente->id)];
            });
    }

    /*
    |--------------------------------------------------------------------------
    | MARCAS
    |--------------------------------------------------------------------------
    */

    public function getMarcasProperty()
    {
        return Marcas::pluck(
            'nombre',
            'id'
        );
    }

    public function updated(
        $propertyName
    ): void {

        if (
            str_contains(
                $propertyName,
                '.cliente_id'
            )
        ) {

            preg_match(
                '/tiendas\.(\d+)\.cliente_id/',
                $propertyName,
                $matches
            );

            if (!isset($matches[1])) {
                return;
            }

            $index = $matches[1];

            $clienteId =
                $this->tiendas[$index]['cliente_id'];

            if (!$clienteId) {

                $this->tiendas[$index]['telefono_referencia'] = null;

                return;
            }

            $cliente =
                Clientes::find($clienteId);

            $this->tiendas[$index]['telefono_referencia'] =

                trim(
                    ($cliente?->codigo_pais ?? '') .
                        ' ' .
                        ($cliente?->numero_celular ?? '')
                );
        }
    }
}
