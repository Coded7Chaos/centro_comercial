<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            $suscripcion->generarCobrosMensuales();
        });
    }

    public function generarCobrosMensuales(): void
    {
        if ($this->cobros()->exists()) {
            return;
        }

        $start = Carbon::parse($this->fecha_inicio);
        $totalMonths = $this->getDuracionEnMeses();

        $monthlyRent = round((float) $this->precio / $totalMonths, 2);
        $isRenewal = $this->renovacion_de_id !== null;
        $pagoInicial = ($totalMonths > 1 && ! $isRenewal) ? $monthlyRent * 2 : $monthlyRent;

        $tienda = $this->infraestructurasTienda;
        $tiendaNombre = $tienda?->nombre ?: ($tienda ? 'Tienda #'.$tienda->numero : 'Sin nombre');

        for ($i = 0; $i < $totalMonths; $i++) {
            $fechaVencimiento = $start->copy()->addMonthsNoOverflow($i)->toDateString();

            $monto = ($i === 0) ? $pagoInicial : $monthlyRent;

            $concepto = 'Cobro Mensual #'.($i + 1).' - '.$tiendaNombre;
            if ($i === 0 && $totalMonths > 1 && ! $isRenewal) {
                $concepto = 'Cobro Mensual #1 + Garantía - '.$tiendaNombre;
            }

            SuscripcionesCobros::create([
                'suscripcion_id' => $this->id,
                'concepto' => $concepto,
                'monto' => $monto,
                'fecha_inicio' => $fechaVencimiento,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'pendiente',
                'observaciones' => 'Cobro generado automáticamente',
                'es_parcial' => false,
            ]);
        }
    }

    public function getDuracionEnMeses(): int
    {
        $tipo = strtolower((string) $this->tipo);

        if (preg_match('/\d+/', $tipo, $matches)) {
            $valor = max(1, (int) $matches[0]);

            return str_contains($tipo, 'año') ? $valor * 12 : $valor;
        }

        $start = Carbon::parse($this->fecha_inicio);
        $end = Carbon::parse($this->fecha_fin)->addDay();

        return (int) max(1, round($start->diffInMonths($end)));
    }
}
