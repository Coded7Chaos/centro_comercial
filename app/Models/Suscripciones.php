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
        'renovacion_de_id',
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

    public function renovadaDe(): BelongsTo
    {
        return $this->belongsTo(Suscripciones::class, 'renovacion_de_id');
    }

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
        | CREAR COBROS MENSUALES
        |--------------------------------------------------------------------------
        */

            $start = \Carbon\Carbon::parse($suscripcion->fecha_inicio);
            $end = \Carbon\Carbon::parse($suscripcion->fecha_fin)->addDay();
            $totalMonths = (int) max(1, round($start->diffInMonths($end)));

            $monthlyRent = (float)$suscripcion->precio / $totalMonths;
            $isRenewal = $suscripcion->renovacion_de_id !== null;
            $pago_inicial = ($totalMonths > 1 && !$isRenewal) ? $monthlyRent * 2 : $monthlyRent;

            $tienda = $suscripcion->infraestructurasTienda;
            $tiendaNombre = $tienda?->nombre ?: ($tienda ? 'Tienda #' . $tienda->numero : 'Sin nombre');

            for ($i = 0; $i < $totalMonths; $i++) {
                $fechaVencimiento = $start->copy()->addMonths($i)->toDateString();
                
                $monto = ($i === 0) ? $pago_inicial : $monthlyRent;
                
                $concepto = 'Cobro Mensual #' . ($i + 1) . ' - ' . $tiendaNombre;
                if ($i === 0 && $totalMonths > 1 && !$isRenewal) {
                    $concepto = 'Cobro Mensual #1 + Garantía - ' . $tiendaNombre;
                }

                SuscripcionesCobros::create([
                    'suscripcion_id' => $suscripcion->id,
                    'concepto' => $concepto,
                    'monto' => $monto,
                    'fecha_inicio' => $fechaVencimiento,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado' => 'pendiente',
                    'observaciones' => 'Cobro generado automáticamente',
                    'es_parcial' => false,
                ]);
            }
        });
    }
}
