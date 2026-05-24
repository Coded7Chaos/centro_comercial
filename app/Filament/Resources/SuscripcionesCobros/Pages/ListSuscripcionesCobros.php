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

class ListSuscripcionesCobros extends ListRecords
{
    protected static string $resource = SuscripcionesCobrosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('generar_cobros')
                ->label('Generar cobros del mes')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->action(fn() => $this->generarCobrosMensuales()),
        ];
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
