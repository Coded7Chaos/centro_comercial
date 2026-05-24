<?php

namespace App\Filament\Resources\SuscripcionesPagos\Schemas;

use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\SuscripcionesCobros;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class SuscripcionesPagosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

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

                                'Cliente #'

                                    . $cliente->id

                                    . ' - '

                                    . $cliente->nombre_completo
                            ];
                        })

                )

                ->searchable()

                ->preload()

                ->live()

                ->dehydrated(false)

                ->afterStateUpdated(function (Set $set) {

                    $set('tienda_id', null);

                    $set('suscripcion_cobro_id', null);

                    $set('localizacion', null);

                    $set('monto_total', null);

                    $set('monto_pagado', null);

                    $set('pago_pendiente', null);
                }),

            /*
            |--------------------------------------------------------------------------
            | TIENDA
            |--------------------------------------------------------------------------
            */

            Select::make('tienda_id')

                ->label('Tienda')

                ->disabled(
                    fn(Get $get) =>
                    !$get('cliente_id')
                )

                ->options(function (Get $get) {

                    if (!$get('cliente_id')) {
                        return [];
                    }

                    return InfraestructurasTiendas::where(
                        'cliente_id',
                        $get('cliente_id')
                    )

                        ->get()

                        ->mapWithKeys(function ($tienda) {

                            return [

                                $tienda->id =>

                                'Tienda #'

                                    . $tienda->numero

                                    . ' - '

                                    . ($tienda->nombre ?? 'Sin nombre')
                            ];
                        });
                })

                ->searchable()

                ->preload()

                ->live()

                ->dehydrated(false)

                ->afterStateUpdated(function (
                    Get $get,
                    Set $set,
                    $state
                ) {

                    /*
    |----------------------------------------------------------------------
    | LIMPIAR CAMPOS
    |----------------------------------------------------------------------
    */

                    $set('localizacion', null);

                    $set('monto_total', null);

                    $set('monto_pagado', null);

                    $set('pago_pendiente', null);

                    $set('fecha_vencimiento', null);

                    if (!$state) {
                        return;
                    }

                    /*
    |----------------------------------------------------------------------
    | BUSCAR COBRO PENDIENTE
    |----------------------------------------------------------------------
    */

                    $cobro = SuscripcionesCobros::with([
                        'suscripcion.infraestructurasTienda.piso.infraestructura'
                    ])

                        ->whereHas(
                            'suscripcion',
                            function ($query) use ($state) {

                                $query->where(
                                    'infraestructuras_tienda_id',
                                    $state
                                );
                            }
                        )

                        ->whereIn('estado', [
                            'pendiente',
                            'parcial',
                        ])

                        ->latest()

                        ->first();

                    if (!$cobro) {

                        /*
    |----------------------------------------------------------------------
    | LIMPIAR CAMPOS
    |----------------------------------------------------------------------
    */

                        $set('suscripcion_cobro_id', null);

                        $set('localizacion', null);

                        $set('monto_total', null);

                        $set('total_pagado', null);

                        $set('monto_pagado', null);

                        $set('pago_pendiente', null);

                        $set('saldo_restante', null);

                        $set('fecha_vencimiento', null);

                        /*
    |----------------------------------------------------------------------
    | NOTIFICACIÓN
    |----------------------------------------------------------------------
    */

                        Notification::make()

                            ->title('Sin deudas pendientes')

                            ->body(
                                'La tienda seleccionada ya tiene todos sus pagos completados.'
                            )

                            ->warning()

                            ->send();

                        return;
                    }

                    /*
|----------------------------------------------------------------------
| CON DEUDA
|----------------------------------------------------------------------
*/

                    /*
    |----------------------------------------------------------------------
    | RELACIONES
    |----------------------------------------------------------------------
    */

                    $suscripcion =
                        $cobro->suscripcion;

                    $tienda =
                        $suscripcion?->infraestructurasTienda;

                    $piso =
                        $tienda?->piso;

                    $infra =
                        $piso?->infraestructura;

                    /*
    |----------------------------------------------------------------------
    | LOCALIZACIÓN
    |----------------------------------------------------------------------
    */

                    $set(

                        'localizacion',

                        ($infra?->nombre ?? 'Sin infraestructura')

                            . ' - Piso '

                            . ($piso?->nombre ?? '---')
                    );

                    /*
    |----------------------------------------------------------------------
    | MONTOS
    |----------------------------------------------------------------------
    */

                    $pagado = $cobro->pagos()
                        ->sum('monto_pagado');

                    $pendiente =
                        $cobro->monto - $pagado;

                    $set(
                        'monto_total',
                        $cobro->monto
                    );

                    $set(
                        'total_pagado',
                        $pagado
                    );

                    /*
|----------------------------------------------------------------------
| NUEVO PAGO
|----------------------------------------------------------------------
*/

                    $set(
                        'monto_pagado',
                        $pendiente
                    );

                    /*
|----------------------------------------------------------------------
| PENDIENTE
|----------------------------------------------------------------------
*/

                    $set(
                        'pago_pendiente',
                        $pendiente
                    );

                    $set(
                        'saldo_restante',
                        $pendiente
                    );

                    /*
    |----------------------------------------------------------------------
    | FECHAS
    |----------------------------------------------------------------------
    */

                    $set(
                        'fecha_vencimiento',
                        $cobro->fecha_vencimiento
                    );

                    $set(
                        'fecha_pago',
                        now()->format('Y-m-d')
                    );

                    $set(
                        'hora_pago',
                        now()->format('H:i:s')
                    );

                    /*
    |----------------------------------------------------------------------
    | COBRO ID
    |----------------------------------------------------------------------
    */

                    $set(
                        'suscripcion_cobro_id',
                        $cobro->id
                    );
                })

                ->required(),

            ///Add///

            Select::make('suscripcion_cobro_id')

                ->label('Cobro pendiente')

                ->disabled()

                ->dehydrated()

                ->options(function (Get $get) {

                    if (!$get('suscripcion_cobro_id')) {
                        return [];
                    }

                    $cobro = SuscripcionesCobros::with(
                        'suscripcion'
                    )->find($get('suscripcion_cobro_id'));

                    if (!$cobro) {
                        return [];
                    }

                    $suscripcion =
                        $cobro->suscripcion;

                    return [

                        $cobro->id =>

                        'Cobro #'

                            . $cobro->id

                            . ' - '

                            . ucfirst($suscripcion?->tipo)

                            . ' - Bs '

                            . $cobro->monto
                    ];
                })

                ->afterStateHydrated(function ($state, Set $set) {

                    if (!$state) {
                        return;
                    }

                    $cobro = SuscripcionesCobros::with([
                        'suscripcion.cliente',
                        'suscripcion.infraestructurasTienda.piso.infraestructura',
                        'pagos',
                    ])->find($state);

                    if (!$cobro) {
                        return;
                    }

                    $suscripcion = $cobro->suscripcion;

                    $cliente = $suscripcion?->cliente;

                    $tienda = $suscripcion?->infraestructurasTienda;

                    $piso = $tienda?->piso;

                    $infra = $piso?->infraestructura;

                    /*
    |--------------------------------------------------------------------------
    | CLIENTE
    |--------------------------------------------------------------------------
    */

                    $set('cliente_id', $cliente?->id);

                    /*
    |--------------------------------------------------------------------------
    | TIENDA
    |--------------------------------------------------------------------------
    */

                    $set('tienda_id', $tienda?->id);

                    /*
    |--------------------------------------------------------------------------
    | LOCALIZACIÓN
    |--------------------------------------------------------------------------
    */

                    $set(
                        'localizacion',

                        ($infra?->nombre ?? 'Sin infraestructura')

                            . ' - Piso '

                            . ($piso?->nombre ?? '---')
                    );

                    /*
    |--------------------------------------------------------------------------
    | MONTOS
    |--------------------------------------------------------------------------
    */

                    $pagado = $cobro->pagos()
                        ->sum('monto_pagado');

                    $pendiente = $cobro->monto - $pagado;

                    $set('monto_total', $cobro->monto);

                    $set('total_pagado', $pagado);

                    /*
|----------------------------------------------------------------------
| NUEVO PAGO
|----------------------------------------------------------------------
*/

                    $set('monto_pagado', $pendiente);

                    /*
|----------------------------------------------------------------------
| PENDIENTE
|----------------------------------------------------------------------
*/

                    $set('pago_pendiente', $pendiente);
                    $set('saldo_restante', $pendiente);

                    /*
    |--------------------------------------------------------------------------
    | FECHAS
    |--------------------------------------------------------------------------
    */

                    $set(
                        'fecha_vencimiento',
                        $cobro->fecha_vencimiento
                    );
                }),

            /*
            |--------------------------------------------------------------------------
            | LOCALIZACIÓN
            |--------------------------------------------------------------------------
            */

            TextInput::make('localizacion')

                ->label('Localización')

                ->disabled()

                ->dehydrated(),

            TextInput::make('total_pagado')

                ->label('Total pagado hasta ahora')

                ->prefix('Bs')

                ->disabled()

                ->dehydrated(false),

            /*
            |--------------------------------------------------------------------------
            | MONTO PAGADO
            |--------------------------------------------------------------------------
            */

            TextInput::make('monto_pagado')

                ->label('Nuevo pago')

                ->numeric()

                ->prefix('Bs')

                ->live()

                ->afterStateUpdated(function (
                    Get $get,
                    Set $set,
                    $state
                ) {

                    $total =
                        $get('saldo_restante') ?? 0;

                    if ($state < 0) {
                        $state = 0;
                    }

                    if ($state > $total) {
                        $state = $total;
                    }

                    $set(
                        'monto_pagado',
                        $state
                    );

                    $nuevoPendiente = $total - $state;

                    if ($nuevoPendiente < 0) {
                        $nuevoPendiente = 0;
                    }

                    $set(
                        'pago_pendiente',
                        $nuevoPendiente
                    );
                })

                ->required(),

            /*
            |--------------------------------------------------------------------------
            | MONTO TOTAL
            |--------------------------------------------------------------------------
            */

            TextInput::make('monto_total')

                ->hidden()

                ->dehydrated(false),

            TextInput::make('suscripcion_cobro_id')

                ->hidden()

                ->dehydrated(),

            /*
            |--------------------------------------------------------------------------
            | PAGO PENDIENTE
            |--------------------------------------------------------------------------
            */

            TextInput::make('pago_pendiente')

                ->label('Pago pendiente')

                ->disabled()

                ->dehydrated(),

            /*
            |--------------------------------------------------------------------------
            | FECHA PAGO
            |--------------------------------------------------------------------------
            */

            DatePicker::make('fecha_pago')

                ->label('Fecha de pago')

                ->default(now())

                ->minDate(today())

                ->required(),

            /*
            |--------------------------------------------------------------------------
            | FECHA VENCIMIENTO
            |--------------------------------------------------------------------------
            */

            DatePicker::make('fecha_vencimiento')

                ->label('Fecha de vencimiento')

                ->disabled()

                ->dehydrated(),

            /*
|--------------------------------------------------------------------------
| MÉTODO DE PAGO
|--------------------------------------------------------------------------
*/

            Select::make('metodo_pago')

                ->label('Método de pago')

                ->options([

                    'efectivo' => 'Efectivo',

                    'transferencia' => 'Transferencia Bancaria',

                    'qr' => 'QR',

                    'tarjeta' => 'Tarjeta',
                ])

                ->required()

                ->live(),

            /*
|--------------------------------------------------------------------------
| EFECTIVO
|--------------------------------------------------------------------------
*/

            TextInput::make('nombre_pagador')

                ->label('Nombre de quien realizó el pago')

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'efectivo'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'efectivo'
                ),

            /*
|--------------------------------------------------------------------------
| TRANSFERENCIA
|--------------------------------------------------------------------------
*/

            TextInput::make('referencia')

                ->label('Número de Transacción / Clave de rastreo')

                ->numeric()

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'transferencia'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'transferencia'
                ),

            Select::make('banco_origen')

                ->label('Banco de origen')

                ->options([

                    'Banco Unión' => 'Banco Unión',

                    'BCP' => 'BCP',

                    'Mercantil Santa Cruz' => 'Mercantil Santa Cruz',

                    'BISA' => 'BISA',

                    'BNB' => 'BNB',

                    'Ganadero' => 'Ganadero',

                    'Ecofuturo' => 'Ecofuturo',

                    'otro' => 'Otro',
                ])

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'transferencia'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'transferencia'
                )

                ->live(),

            TextInput::make('otro_banco')

                ->label('Nombre del banco')

                ->visible(
                    fn(Get $get) =>

                    $get('metodo_pago') === 'transferencia'
                        &&
                        $get('banco_origen') === 'otro'
                ),

            TextInput::make('nombre_titular')

                ->label('Nombre del titular')

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'transferencia'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'transferencia'
                ),

            /*
|--------------------------------------------------------------------------
| QR
|--------------------------------------------------------------------------
*/

            TextInput::make('folio_qr')

                ->label('ID de operación / Folio QR')

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'qr'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'qr'
                ),

            Select::make('billetera_origen')

                ->label('Billetera / Aplicación origen')

                ->options([

                    'Yape' => 'Yape',

                    'Tigo Money' => 'Tigo Money',

                    'Pix' => 'Pix',

                    'CoDi' => 'CoDi',

                    'Otro' => 'Otro',
                ])

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'qr'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'qr'
                )

                ->live(),

            TextInput::make('otra_billetera')

                ->label('Nombre de la aplicación')

                ->visible(
                    fn(Get $get) =>

                    $get('metodo_pago') === 'qr'
                        &&
                        $get('billetera_origen') === 'Otro'
                ),

            /*
|--------------------------------------------------------------------------
| TARJETA
|--------------------------------------------------------------------------
*/

            TextInput::make('codigo_autorizacion')

                ->label('Código de autorización')

                ->numeric()

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'tarjeta'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'tarjeta'
                ),

            TextInput::make('ultimos_4')

                ->label('Últimos 4 dígitos')

                ->numeric()

                ->minLength(4)

                ->maxLength(4)

                ->required(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'tarjeta'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'tarjeta'
                ),

            Select::make('marca_tarjeta')

                ->label('Marca de tarjeta')

                ->options([

                    'Visa' => 'Visa',

                    'Mastercard' => 'Mastercard',

                    'American Express' => 'American Express',

                    'Otro' => 'Otro',
                ])

                ->visible(
                    fn(Get $get) =>
                    $get('metodo_pago') === 'tarjeta'
                ),

            /*
|--------------------------------------------------------------------------
| COMPROBANTE
|--------------------------------------------------------------------------
*/

            FileUpload::make('comprobante')

                ->label('Comprobante')

                ->directory('pagos/comprobantes')

                ->acceptedFileTypes([

                    'image/png',

                    'image/jpeg',

                    'application/pdf',
                ])

                ->required(
                    fn(Get $get) =>

                    in_array(

                        $get('metodo_pago'),

                        [
                            'transferencia',
                            'qr'
                        ]
                    )
                )

                ->visible(
                    fn(Get $get) =>

                    in_array(

                        $get('metodo_pago'),

                        [
                            'transferencia',
                            'qr'
                        ]
                    )
                ),

            /*
|--------------------------------------------------------------------------
| HORA DE PAGO
|--------------------------------------------------------------------------
*/

            TextInput::make('hora_pago')

                ->label('Hora de pago')

                ->default(now()->format('H:i:s'))

                ->disabled()

                ->dehydrated(),

            /*
|--------------------------------------------------------------------------
| OBSERVACIONES
|--------------------------------------------------------------------------
*/

            Textarea::make('observaciones')

                ->label('Observaciones')

                ->placeholder('Sin observaciones')

                ->columnSpanFull()

                ->dehydrateStateUsing(

                    fn($state) =>

                    filled($state)

                        ? $state

                        : 'Sin observaciones'
                ),
        ]);
    }
}
