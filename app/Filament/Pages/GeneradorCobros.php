<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class GeneradorCobros extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'Generar Cobros Masivos';
    protected static ?string $title = 'Generador de Cobros Mensuales';
    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';
    protected string $view = 'filament.pages.generador-cobros';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:GeneradorCobros') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar')
                ->label('Generar Cobros del Mes (' . now()->translatedFormat('F Y') . ')')
                ->requiresConfirmation()
                ->modalHeading('¿Generar cobros para todas las tiendas?')
                ->modalDescription('Esto creará un nuevo registro de cobro para cada suscripción activa si aún no tiene uno en este mes.')
                ->modalSubmitActionLabel('Sí, Generar')
                ->action(function () {
                    $suscripciones = Suscripciones::all();
                    $contador = 0;
                    
                    foreach($suscripciones as $suscripcion) {
                        $mesActual = now()->month;
                        $anioActual = now()->year;
                        
                        $existe = SuscripcionesCobros::where('suscripcion_id', $suscripcion->id)
                            ->whereMonth('fecha_inicio', $mesActual)
                            ->whereYear('fecha_inicio', $anioActual)
                            ->exists();
                            
                        if(!$existe) {
                            SuscripcionesCobros::create([
                                'suscripcion_id' => $suscripcion->id,
                                'concepto' => 'Alquiler del mes de ' . now()->translatedFormat('F Y'),
                                'monto' => $suscripcion->precio,
                                'fecha_inicio' => now()->startOfMonth(),
                                'fecha_vencimiento' => now()->startOfMonth()->addDays(5),
                                'estado' => 'pendiente',
                            ]);
                            $contador++;
                        }
                    }
                    
                    Notification::make()
                        ->title('Proceso Completado')
                        ->body("Se han generado {$contador} cobros nuevos exitosamente.")
                        ->success()
                        ->send();
                })
        ];
    }
}
