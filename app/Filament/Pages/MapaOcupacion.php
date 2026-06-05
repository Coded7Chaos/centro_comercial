<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;

class MapaOcupacion extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';
    protected static string|\UnitEnum|null $navigationGroup = 'Infraestructura';
    protected string $view = 'filament.pages.mapa-ocupacion';

    public ?int $selectedPisoId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:MapaOcupacion') ?? false;
    }

    public function selectPiso(int $pisoId): void
    {
        $this->selectedPisoId = $pisoId;
    }

    protected function getViewData(): array
    {
        $infraId = \App\Support\ActiveInfraestructura::getId();
        $pisos = $infraId
            ? InfraestructurasPisos::where('infraestructura_id', $infraId)->get()
            : InfraestructurasPisos::all();
        $estadisticas = [];

        foreach ($pisos as $piso) {
            $totalTiendas = InfraestructurasTiendas::where('infraestructura_piso_id', $piso->id)->count();
            $ocupadas = InfraestructurasTiendas::where('infraestructura_piso_id', $piso->id)
                ->whereHas('estado', fn ($q) => $q->where('estado', 'Alquilada'))
                ->count();

            $disponibles = max(0, $totalTiendas - $ocupadas);
            $porcentaje = $totalTiendas > 0 ? round(($ocupadas / $totalTiendas) * 100) : 0;

            $estadisticas[] = [
                'id' => $piso->id,
                'numero' => $piso->numero,
                'piso' => $piso->nombre,
                'total' => $totalTiendas,
                'ocupadas' => $ocupadas,
                'disponibles' => $disponibles,
                'porcentaje' => $porcentaje
            ];
        }

        $tiendas = [];
        $selectedPiso = null;
        if ($this->selectedPisoId) {
            $selectedPiso = InfraestructurasPisos::find($this->selectedPisoId);
            if ($selectedPiso) {
                $tiendas = InfraestructurasTiendas::where('infraestructura_piso_id', $this->selectedPisoId)
                    ->with(['marcas', 'cliente.user', 'estado'])
                    ->get()
                    ->map(function ($tienda) {
                        $isOccupied = $tienda->estado?->estado === 'Alquilada';
                        
                        if ($isOccupied) {
                            // Find active subscription
                            $activeSub = Suscripciones::where('infraestructuras_tienda_id', $tienda->id)
                                ->where('fecha_inicio', '<=', now())
                                ->where('fecha_fin', '>=', now())
                                ->first();

                            if (!$activeSub) {
                                $activeSub = Suscripciones::where('infraestructuras_tienda_id', $tienda->id)
                                    ->latest('fecha_inicio')
                                    ->first();
                            }

                            $proximoCobro = null;
                            if ($activeSub) {
                                $proximoCobro = $activeSub->cobros()
                                    ->where('estado', '!=', 'pagado')
                                    ->orderBy('fecha_vencimiento', 'asc')
                                    ->first();
                            }

                            return [
                                'id' => $tienda->id,
                                'numero' => $tienda->numero,
                                'nombre' => $tienda->nombre ?: 'Sin Nombre Comercial',
                                'ocupada' => true,
                                'marcas' => $tienda->marcas->pluck('nombre')->implode(', ') ?: 'Sin marcas',
                                'cliente' => $tienda->cliente ? $tienda->cliente->nombre_completo : 'N/A',
                                'contacto' => $tienda->cliente ? $tienda->cliente->numero_celular : 'N/A',
                                'fecha_inicio' => $activeSub ? \Carbon\Carbon::parse($activeSub->fecha_inicio)->format('d/m/Y') : 'N/A',
                                'fecha_fin' => $activeSub ? \Carbon\Carbon::parse($activeSub->fecha_fin)->format('d/m/Y') : 'N/A',
                                'fecha_proximo_pago' => $proximoCobro ? \Carbon\Carbon::parse($proximoCobro->fecha_vencimiento)->format('d/m/Y') : 'Al día / Sin cobros',
                            ];
                        } else {
                            $fechaLibreDesde = $tienda->getFechaLibreDesde();
                            $diasLibre = $tienda->getDiasLibre();
                            $costoOportunidad = $tienda->getCostoOportunidad();

                            return [
                                'id' => $tienda->id,
                                'numero' => $tienda->numero,
                                'ocupada' => false,
                                'fecha_libre_desde' => $fechaLibreDesde->format('d/m/Y'),
                                'dias_libre' => $diasLibre,
                                'costo_oportunidad' => $costoOportunidad,
                            ];
                        }
                    })
                    ->toArray();
            }
        }

        return [
            'estadisticas' => $estadisticas,
            'tiendas' => $tiendas,
            'selectedPiso' => $selectedPiso,
        ];
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Análisis de Ocupación Física';
    }
}
