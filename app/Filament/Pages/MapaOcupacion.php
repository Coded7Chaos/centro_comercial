<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;

class MapaOcupacion extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';
    protected static string|\UnitEnum|null $navigationGroup = 'Infraestructura';
    protected string $view = 'filament.pages.mapa-ocupacion';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:MapaOcupacion') ?? false;
    }

    protected function getViewData(): array
    {
        $pisos = InfraestructurasPisos::all();
        $estadisticas = [];

        foreach ($pisos as $piso) {
            $totalTiendas = InfraestructurasTiendas::where('infraestructura_piso_id', $piso->id)->count();
            $ocupadas = InfraestructurasTiendas::where('infraestructura_piso_id', $piso->id)
                ->whereHas('estado', fn ($q) => $q->where('estado', 'Alquilada'))
                ->count();

            $disponibles = max(0, $totalTiendas - $ocupadas);
            $porcentaje = $totalTiendas > 0 ? round(($ocupadas / $totalTiendas) * 100) : 0;

            $estadisticas[] = [
                'piso' => $piso->nombre,
                'total' => $totalTiendas,
                'ocupadas' => $ocupadas,
                'disponibles' => $disponibles,
                'porcentaje' => $porcentaje
            ];
        }

        return [
            'estadisticas' => $estadisticas
        ];
    }
    
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Análisis de Ocupación Física';
    }
}
