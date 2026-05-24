<?php

namespace App\Filament\Resources\Infraestructuras\Pages;

use App\Filament\Resources\Infraestructuras\InfraestructurasResource;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class InfraestructurasPisosDiseno extends Page
{
    use InteractsWithForms;
    protected static string $resource =
    InfraestructurasResource::class;

    protected string $view =
    'filament.resources.infraestructuras.pages.infraestructuras-pisos-diseno';

    public Infraestructuras $record;

    public array $pisos = [];

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('Update:InfraestructurasPisos') ?? false;
    }

    public function mount(
        Infraestructuras $record
    ): void {

        $this->record = $record;

        $pisosExistentes =
            $record->pisosInfraestructura()
            ->orderBy('id')
            ->get();

        $cantidadActual =
            $pisosExistentes->count();

        $cantidadDeseada =
            (int) $record->pisos;

        // =========================
        // AGREGAR PISOS FALTANTES
        // =========================
        if ($cantidadActual < $cantidadDeseada) {

            for (
                $i = $cantidadActual + 1;
                $i <= $cantidadDeseada;
                $i++
            ) {

                $nombre = $i === 1
                    ? 'PB'
                    : 'P' . ($i - 1);

                InfraestructurasPisos::create([

                    'infraestructura_id' =>
                    $record->id,

                    'nombre' =>
                    $nombre,

                    'cantidad_tiendas' => 0,

                    'estado' => 'activo',
                ]);
            }
        }

        // =========================
        // ELIMINAR SOBRANTES
        // =========================
        elseif ($cantidadActual > $cantidadDeseada) {

            $pisosAEliminar =
                $pisosExistentes
                ->slice($cantidadDeseada);

            foreach ($pisosAEliminar as $piso) {

                $piso->delete();
            }
        }

        // =========================
        // RECARGAR PISOS
        // =========================
        $pisosFinales =
            $record->fresh()
            ->pisosInfraestructura()
            ->orderBy('id')
            ->get();

        foreach ($pisosFinales as $piso) {

            $this->pisos[] = [

                'id' => $piso->id,

                'nombre' => $piso->nombre,

                'cantidad_tiendas' =>
                $piso->cantidad_tiendas,

                'estado' => $piso->estado,
            ];
        }
    }
    public function guardar(): void
    {
        foreach ($this->pisos as $pisoData) {

            $piso = InfraestructurasPisos::updateOrCreate(

                [
                    'id' => $pisoData['id'] ?? null,
                ],

                [
                    'infraestructura_id' =>
                    $this->record->id,

                    'nombre' =>
                    $pisoData['nombre'],

                    'cantidad_tiendas' =>
                    $pisoData['cantidad_tiendas'],

                    'estado' =>
                    $pisoData['estado'],
                ]
            );

            // =========================
            // SINCRONIZAR TIENDAS
            // =========================

            $cantidadActual =
                $piso->tiendas()->count();

            $cantidadNueva =
                $piso->cantidad_tiendas;

            // CREAR FALTANTES

            if ($cantidadNueva > $cantidadActual) {

                for (
                    $i = $cantidadActual + 1;
                    $i <= $cantidadNueva;
                    $i++
                ) {

                    \App\Models\InfraestructurasTiendas::create([

                        'infraestructura_piso_id' =>
                        $piso->id,

                        'numero' => $i,

                        'estado' => 'disponible',
                    ]);
                }
            }

            // ELIMINAR SOBRANTES
            elseif ($cantidadNueva < $cantidadActual) {

                $sobrantes =
                    $piso->tiendas()
                    ->orderByDesc('numero')
                    ->take($cantidadActual - $cantidadNueva)
                    ->get();

                foreach ($sobrantes as $tienda) {

                    // SOLO ELIMINAR VACÍAS

                    if (
                        !$tienda->cliente_id &&
                        !$tienda->marca_id
                    ) {

                        \App\Models\Suscripciones::where(
                            'infraestructuras_tienda_id',
                            $tienda->id
                        )->delete();

                        $tienda->delete();
                    }
                }
            }

            // SI EL PISO ESTÁ INACTIVO
            // TODAS SUS TIENDAS TAMBIÉN

            // =========================
            // SINCRONIZAR ESTADO TIENDAS
            // SEGÚN ESTADO DEL PISO
            // =========================

            if ($piso->estado === 'inactivo') {

                // TODO EL PISO SE CIERRA

                $piso->tiendas()->update([
                    'estado' => 'inactivo',
                ]);
            } elseif ($piso->estado === 'activo') {

                foreach ($piso->tiendas as $tienda) {

                    // SI TIENE DUEÑO -> ACTIVO

                    if (
                        $tienda->cliente_id ||
                        $tienda->marca_id
                    ) {

                        $tienda->update([
                            'estado' => 'activo',
                        ]);
                    }

                    // SI NO TIENE DUEÑO -> DISPONIBLE

                    else {

                        $tienda->update([
                            'estado' => 'disponible',
                        ]);
                    }
                }
            }
        }

        Notification::make()
            ->title(
                'Pisos guardados correctamente'
            )
            ->success()
            ->send();
    }
}
