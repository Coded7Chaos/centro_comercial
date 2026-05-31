<?php

namespace App\Filament\Resources\SuscripcionesCobros\Pages;

use App\Filament\Resources\SuscripcionesCobros\SuscripcionesCobrosResource;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSuscripcionesCobros extends ListRecords
{
    protected static string $resource = SuscripcionesCobrosResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'mensuales' => Tab::make('Cobros mensuales')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('es_parcial', false)
                    ->whereNotIn('estado', ['pagado', 'anulado'])
                    ->where('fecha_vencimiento', '>=', now()->toDateString())
                ),
            'morosos' => Tab::make('Morosos')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('es_parcial', false)
                    ->whereNotIn('estado', ['pagado', 'anulado'])
                    ->where('fecha_vencimiento', '<', now()->toDateString())
                ),
            'parciales' => Tab::make('Cobros parciales')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('es_parcial', true)
                    ->whereNotIn('estado', ['pagado', 'anulado'])
                ),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'mensuales';
    }

    public function generarCobrosMensuales(): void
    {
        $suscripciones = Suscripciones::with('infraestructurasTienda')->get();

        $creados = 0;

        foreach ($suscripciones as $suscripcion) {

            // evitar duplicados por mes de vencimiento
            $existe = SuscripcionesCobros::where('suscripcion_id', $suscripcion->id)
                ->where('fecha_vencimiento', $suscripcion->fecha_fin)
                ->exists();

            if ($existe) {
                continue;
            }

            $tienda = $suscripcion->infraestructurasTienda;

            SuscripcionesCobros::create([

                'suscripcion_id' => $suscripcion->id,

                'concepto' =>
                'Cobro ' . ucfirst($suscripcion->tipo)
                    . ' - ' . ($tienda?->nombre ?? 'Sin nombre'),

                'monto' => $suscripcion->precio,

                'fecha_inicio' => $suscripcion->fecha_inicio,

                'fecha_vencimiento' => $suscripcion->fecha_fin,

                'fecha_pago' => now(),

                'estado' => 'pendiente',
            ]);

            $creados++;
        }

        Notification::make()
            ->title("Cobros generados: {$creados}")
            ->success()
            ->send();
    }
}
