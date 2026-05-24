<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\SuscripcionesCobros;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SuscripcionesPagos extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['suscripcion_cobro_id', 'monto_pagado', 'pago_pendiente', 'fecha_pago', 'metodo_pago', 'estado'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('suscripciones');
    }

    protected $fillable = [

        /*
        |------------------------------------------------------------------
        | RELACIÓN
        |------------------------------------------------------------------
        */
        'suscripcion_cobro_id',

        /*
        |------------------------------------------------------------------
        | PAGOS
        |------------------------------------------------------------------
        */
        'monto_pagado',
        'pago_pendiente',

        /*
        |------------------------------------------------------------------
        | FECHAS
        |------------------------------------------------------------------
        */
        'fecha_pago',
        'fecha_vencimiento',
        'fecha_hora_operacion',

        /*
        |------------------------------------------------------------------
        | MÉTODO / ESTADO
        |------------------------------------------------------------------
        */
        'metodo_pago',
        'estado',

        /*
        |------------------------------------------------------------------
        | EFECTIVO
        |------------------------------------------------------------------
        */
        'nombre_pagador',

        /*
        |------------------------------------------------------------------
        | TRANSFERENCIA
        |------------------------------------------------------------------
        */
        'numero_transaccion',
        'banco_origen',
        'banco_otro',
        'nombre_titular',
        'titular_transferencia',

        /*
        |------------------------------------------------------------------
        | QR
        |------------------------------------------------------------------
        */
        'folio_qr',
        'codigo_qr',
        'billetera_origen',
        'billetera_qr',
        'billetera_qr_otro',

        /*
        |------------------------------------------------------------------
        | TARJETA
        |------------------------------------------------------------------
        */
        'codigo_autorizacion',
        'ultimos_4_digitos',
        'ultimos_4_tarjeta',
        'marca_tarjeta',
        'marca_tarjeta_otro',

        /*
        |------------------------------------------------------------------
        | VERIFICACIÓN
        |------------------------------------------------------------------
        */
        'estado_verificacion',
        'monto_total',
        'hora_pago',

        /*
        |------------------------------------------------------------------
        | ARCHIVOS
        |------------------------------------------------------------------
        */
        'comprobante',

        /*
        |------------------------------------------------------------------
        | OBSERVACIONES
        |------------------------------------------------------------------
        */
        'observaciones',
        'estado_snapshot',
        'estado_snapshot',
    ];

    /*
    |----------------------------------------------------------------------
    | RELACIÓN CON COBRO
    |----------------------------------------------------------------------
    */
    public function cobro(): BelongsTo
    {
        return $this->belongsTo(
            SuscripcionesCobros::class,
            'suscripcion_cobro_id'
        );
    }

    /*
    |----------------------------------------------------------------------
    | ACCESO DIRECTO A SUSCRIPCIÓN
    |----------------------------------------------------------------------
    */
    public function suscripcion(): HasOneThrough
    {
        return $this->hasOneThrough(
            Suscripciones::class,
            SuscripcionesCobros::class,

            /*
            | Tabla intermedia
            */
            'id',

            /*
            | Tabla final
            */
            'id',

            /*
            | FK local
            */
            'suscripcion_cobro_id',

            /*
            | FK intermedia
            */
            'suscripcion_id'
        );
    }

    /*
|--------------------------------------------------------------------------
| RECALCULAR COBRO AUTOMÁTICAMENTE
|--------------------------------------------------------------------------
*/

    protected static function booted(): void
    {
        static::created(function ($pago) {

            $cobro = $pago->cobro;

            if (!$cobro) {
                return;
            }

            /*
        |--------------------------------------------------------------------------
        | TOTAL PAGADO
        |--------------------------------------------------------------------------
        */

            $totalPagado = self::where(
                'suscripcion_cobro_id',
                $cobro->id
            )->sum('monto_pagado');

            /*
        |--------------------------------------------------------------------------
        | ESTADO
        |--------------------------------------------------------------------------
        */

            if ($totalPagado >= $cobro->monto) {

                $estado = 'pagado';
            } elseif ($totalPagado > 0) {

                $estado = 'parcial';
            } else {

                $estado = now()->toDateString()
                    > $cobro->fecha_vencimiento

                    ? 'vencido'

                    : 'pendiente';
            }

            /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR COBRO
        |--------------------------------------------------------------------------
        */

            $cobro->update([
                'estado' => $estado,
            ]);
        });
    }
}
