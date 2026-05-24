<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\InfraestructurasTiendas;
use App\Models\SuscripcionesCobros;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


class Suscripciones extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cliente_id', 'marca_id', 'tipo', 'precio', 'fecha_inicio', 'fecha_fin', 'infraestructuras_tienda_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('suscripciones');
    }

    protected $fillable = [
        'cliente_id',
        'marca_id',
        'tipo',
        'precio',
        'fecha_inicio',
        'fecha_fin',
        'infraestructuras_tienda_id',

        'infraestructuras_piso_id',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            Clientes::class,
            'cliente_id'
        );
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(
            Marcas::class,
            'marca_id'
        );
    }

    public function cobros(): HasMany
    {
        return $this->hasMany(
            SuscripcionesCobros::class,
            'suscripcion_id'
        );
    }

    public function infraestructurasTienda(): BelongsTo
    {
        return $this->belongsTo(
            InfraestructurasTiendas::class,
            'infraestructuras_tienda_id'
        );
    }

    /*
|--------------------------------------------------------------------------
| CREAR COBRO AUTOMÁTICO
|--------------------------------------------------------------------------
*/

    protected static function booted(): void
    {
        static::created(function ($suscripcion) {

            /*
        |--------------------------------------------------------------------------
        | EVITAR DUPLICADOS
        |--------------------------------------------------------------------------
        */

            $existeCobro = SuscripcionesCobros::where(
                'suscripcion_id',
                $suscripcion->id
            )->exists();

            if ($existeCobro) {
                return;
            }

            /*
        |--------------------------------------------------------------------------
        | CREAR COBRO
        |--------------------------------------------------------------------------
        */

            SuscripcionesCobros::create([
                'suscripcion_id' => $suscripcion->id,
                'concepto' => 'Cobro '
                    . ucfirst($suscripcion->tipo)
                    . ' - '
                    . ($suscripcion->infraestructurasTienda?->nombre ?? 'Sin nombre'),
                'monto' => $suscripcion->precio,
                'fecha_inicio' => $suscripcion->fecha_inicio,
                'fecha_vencimiento' => $suscripcion->fecha_fin,
                'estado' => 'pendiente',
                'observaciones' => 'Cobro generado automáticamente',
            ]);
        });
    }
}
