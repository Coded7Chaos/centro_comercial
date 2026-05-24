<?php

namespace App\Filament\Resources\Suscripciones\Schemas;

use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\SuscripcionesTarifas;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class SuscripcionesForm
{
    public static function configure(
        Schema $schema
    ): Schema {

        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | CLIENTE
                |--------------------------------------------------------------------------
                */

                Select::make('cliente_id')
                    ->label('Cliente')
                    ->options(
                        Clientes::with('user')->get()
                            ->mapWithKeys(function ($cliente) {
                                return [
                                    $cliente->id =>
                                    $cliente->id .
                                        ' - ' .
                                        $cliente->nombre_completo
                                ];
                            })
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(
                        function (Set $set, $state) {
                            $tienda = InfraestructurasTiendas::where('cliente_id', $state)->first();
                            if (!$tienda) {
                                $set('infraestructuras_tienda_id', null);
                                $set('tamano', null);
                                return;
                            }
                            $set('infraestructuras_tienda_id', $tienda->id);
                            $set('tamano', $tienda->tamano);
                        }
                    )
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | TIENDA
                |--------------------------------------------------------------------------
                */

                Select::make('infraestructuras_tienda_id')
                    ->label('Tienda')
                    ->options(function () {
                        return InfraestructurasTiendas::all()
                            ->mapWithKeys(function ($tienda) {
                                return [
                                    $tienda->id =>
                                    'Tienda #' .
                                        $tienda->numero .
                                        ' - ' .
                                        ($tienda->nombre ?? 'Sin nombre')
                                ];
                            });
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $tienda = InfraestructurasTiendas::find($state);
                        if (!$tienda) {
                            $set('tamano', null);
                            return;
                        }
                        $set('tamano', $tienda->tamano);
                    })
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | TAMAÑO (OCULTO)
                |--------------------------------------------------------------------------
                */

                Hidden::make('tamano')
                    ->dehydrated(),

                /*
                |--------------------------------------------------------------------------
                | TIPO
                |--------------------------------------------------------------------------
                */

                Select::make('tipo')
                    ->label('Tipo de suscripción')
                    ->options([
                        'anual' => 'Anual',
                        'semestral' => 'Semestral',
                        'trimestral' => 'Trimestral',
                        'bimestral' => 'Bimestral',
                        'mensual' => 'Mensual',
                        'semanal' => 'Semanas',
                        'personalizado' => 'Personalizado',
                    ])
                    ->default('mensual')
                    ->live()
                    ->afterStateUpdated(
                        function (
                            Get $get,
                            Set $set,
                            $state
                        ) {
                            /*
                            |--------------------------------------------------------------------------
                            | FECHA FIN
                            |--------------------------------------------------------------------------
                            */
                            $inicio = Carbon::parse(
                                $get('fecha_inicio')
                            );

                            switch ($state) {
                                case 'semanal':
                                    $fin = $inicio->copy()->addWeek();
                                    break;
                                case 'mensual':
                                    $fin = $inicio->copy()->addMonth();
                                    break;
                                case 'bimestral':
                                    $fin = $inicio->copy()->addMonths(2);
                                    break;
                                case 'trimestral':
                                    $fin = $inicio->copy()->addMonths(3);
                                    break;
                                case 'semestral':
                                    $fin = $inicio->copy()->addMonths(6);
                                    break;
                                case 'anual':
                                    $fin = $inicio->copy()->addYear();
                                    break;
                                default:
                                    $fin = null;
                            }

                            $set('fecha_fin', $fin?->format('Y-m-d'));

                            /*
                            |--------------------------------------------------------------------------
                            | PRECIO AUTOMÁTICO (busca por rango de m²)
                            |--------------------------------------------------------------------------
                            */
                            if ($state !== 'personalizado') {
                                $tarifa = SuscripcionesTarifas::precioPara(
                                    (float) $get('tamano'),
                                    $state
                                );

                                if ($tarifa) {
                                    $set('precio', $tarifa->precio);
                                }
                            }
                        }
                    )
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | PRECIO
                |--------------------------------------------------------------------------
                */

                TextInput::make('precio')
                    ->label('Precio')
                    ->prefix('Bs.')
                    ->numeric()
                    ->dehydrated()
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | FECHA INICIO
                |--------------------------------------------------------------------------
                */

                DatePicker::make('fecha_inicio')
                    ->default(now())
                    ->live()
                    ->afterStateUpdated(
                        function (
                            Get $get,
                            Set $set,
                            $state
                        ) {
                            if ($get('tipo') === 'personalizado' || !$state) {
                                return;
                            }

                            $inicio = Carbon::parse($state);

                            switch ($get('tipo')) {
                                case 'semanal':
                                    $fin = $inicio->copy()->addWeek();
                                    break;
                                case 'mensual':
                                    $fin = $inicio->copy()->addMonth();
                                    break;
                                case 'bimestral':
                                    $fin = $inicio->copy()->addMonths(2);
                                    break;
                                case 'trimestral':
                                    $fin = $inicio->copy()->addMonths(3);
                                    break;
                                case 'semestral':
                                    $fin = $inicio->copy()->addMonths(6);
                                    break;
                                case 'anual':
                                    $fin = $inicio->copy()->addYear();
                                    break;
                                default:
                                    $fin = null;
                            }

                            $set('fecha_fin', $fin?->format('Y-m-d'));
                        }
                    )
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | FECHA FIN
                |--------------------------------------------------------------------------
                */

                DatePicker::make('fecha_fin')
                    ->disabled(fn(Get $get) => $get('tipo') !== 'personalizado')
                    ->dehydrated()
                    ->required()
                    ->afterStateHydrated(function (Get $get, Set $set) {
                        $tiendaId = $get('infraestructuras_tienda_id');
                        if (!$tiendaId) return;
                        $tienda = InfraestructurasTiendas::find($tiendaId);
                        if (!$tienda) return;
                        $set('tamano', $tienda->tamano);
                    }),
            ]);
    }
}
