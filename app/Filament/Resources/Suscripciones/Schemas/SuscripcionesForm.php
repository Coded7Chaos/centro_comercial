<?php

namespace App\Filament\Resources\Suscripciones\Schemas;

use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\SuscripcionesTarifas;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

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
                                    $cliente->id => $cliente->id.
                                        ' - '.
                                        $cliente->nombre_completo,
                                ];
                            })
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(
                        function (Set $set, $state) {
                            $tienda = InfraestructurasTiendas::where('cliente_id', $state)->first();
                            if (! $tienda) {
                                $set('infraestructuras_tienda_id', null);
                                $set('tamano', null);

                                return;
                            }
                            $set('infraestructuras_tienda_id', $tienda->id);
                            $set('tamano', $tienda->tamano);
                        }
                    )
                    ->required(),

                Select::make('marca_id')
                    ->label('Marca')
                    ->options(function (Get $get) {
                        $clienteId = $get('cliente_id');
                        $query = Marcas::query()->whereNull('cliente_id');
                        if ($clienteId) {
                            $query->orWhere('cliente_id', $clienteId);
                        }

                        return $query->pluck('nombre', 'id');
                    })
                    ->searchable()
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
                                    $tienda->id => 'Tienda #'.
                                        $tienda->numero.
                                        ' - '.
                                        ($tienda->nombre ?? 'Sin nombre'),
                                ];
                            });
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $tienda = InfraestructurasTiendas::find($state);
                        if (! $tienda) {
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
                    ->label('Duración del contrato')
                    ->options([
                        '1 mes' => '1 mes',
                        '2 meses' => '2 meses',
                        '3 meses' => '3 meses',
                        '4 meses' => '4 meses',
                        '5 meses' => '5 meses',
                        '6 meses' => '6 meses',
                        '7 meses' => '7 meses',
                        '8 meses' => '8 meses',
                        '9 meses' => '9 meses',
                        '10 meses' => '10 meses',
                        '11 meses' => '11 meses',
                        '12 meses' => '12 meses',
                        '1 año' => '1 año',
                        '2 años' => '2 años',
                        'personalizado' => 'Personalizado',
                    ])
                    ->default('1 mes')
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

                            if (! $state || $state === 'personalizado') {
                                $fin = null;
                            } else {
                                preg_match('/\d+/', strtolower($state), $matches);
                                $val = isset($matches[0]) ? (int) $matches[0] : 1;
                                $months = str_contains(strtolower($state), 'año') ? $val * 12 : $val;
                                $fin = $inicio->copy()->addMonthsNoOverflow($months)->subDay();
                            }

                            $set('fecha_fin', $fin?->format('Y-m-d'));

                            /*
                            |--------------------------------------------------------------------------
                            | PRECIO AUTOMÁTICO
                            |--------------------------------------------------------------------------
                            */
                            if ($state && $state !== 'personalizado') {
                                preg_match('/\d+/', strtolower($state), $matches);
                                $val = isset($matches[0]) ? (int) $matches[0] : 1;
                                $months = str_contains(strtolower($state), 'año') ? $val * 12 : $val;
                                $tamano = (float) $get('tamano');
                                if ($tamano > 0) {
                                    $calc = SuscripcionesTarifas::calcularAlquiler($tamano, $months);
                                    $set('precio', $calc['precio_total_con_descuento']);
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
                            $tipoState = $get('tipo');
                            if (! $tipoState || $tipoState === 'personalizado' || ! $state) {
                                $fin = null;
                            } else {
                                $inicio = Carbon::parse($state);
                                preg_match('/\d+/', strtolower($tipoState), $matches);
                                $val = isset($matches[0]) ? (int) $matches[0] : 1;
                                $months = str_contains(strtolower($tipoState), 'año') ? $val * 12 : $val;
                                $fin = $inicio->copy()->addMonthsNoOverflow($months)->subDay();
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
                    ->disabled(fn (Get $get) => $get('tipo') !== 'personalizado')
                    ->dehydrated()
                    ->required()
                    ->afterStateHydrated(function (Get $get, Set $set) {
                        $tiendaId = $get('infraestructuras_tienda_id');
                        if (! $tiendaId) {
                            return;
                        }
                        $tienda = InfraestructurasTiendas::find($tiendaId);
                        if (! $tienda) {
                            return;
                        }
                        $set('tamano', $tienda->tamano);
                    }),

                FileUpload::make('contrato_firmado')
                    ->label('Contrato Firmado (PDF/Imagen)')
                    ->disk('public')
                    ->directory('contratos-firmados')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240)
                    ->nullable(),
            ]);
    }
}
