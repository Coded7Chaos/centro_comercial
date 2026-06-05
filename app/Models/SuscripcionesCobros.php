<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


class SuscripcionesCobros extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['suscripcion_id', 'concepto', 'monto', 'fecha_inicio', 'fecha_vencimiento', 'fecha_pago', 'estado'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('suscripciones');
    }

    protected $fillable = [
        'suscripcion_id',
        'concepto',
        'monto',
        'fecha_inicio',
        'fecha_vencimiento',
        'fecha_pago',
        'estado',
        'observaciones',
        'saldo_pendiente',
        'estado_snapshot',
        'es_parcial',
    ];

    protected static function booted(): void
    {
        static::creating(function ($cobro) {
            if ($cobro->saldo_pendiente === null) {
                $cobro->saldo_pendiente = $cobro->monto;
            }
            if ($cobro->estado_snapshot === null) {
                $cobro->estado_snapshot = $cobro->estado;
            }
        });
    }

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(
            Suscripciones::class,
            'suscripcion_id'
        );
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(
            SuscripcionesPagos::class,
            'suscripcion_cobro_id'
        );
    }

    public function recalcularEstado(): void
    {
        $totalPagado = $this->pagos()->where('estado_verificacion', 'verificado')->sum('monto_pagado');

        if ($totalPagado >= $this->monto) {
            $estado = 'pagado';
        } elseif ($totalPagado > 0) {
            $estado = 'parcial';
        } else {
            $hasPending = $this->pagos()->where('estado_verificacion', 'pendiente')->exists();
            if ($hasPending) {
                $estado = 'pendiente_confirmacion';
            } else {
                $estado = now()->toDateString() > $this->fecha_vencimiento
                    ? 'vencido'
                    : 'pendiente';
            }
        }

        $this->update(['estado' => $estado]);
    }

    /**
     * Fecha de vencimiento para el cobro de saldo parcial pendiente:
     * el mes siguiente al cobro original.
     */
    public static function fechaSaldoPendiente(self $cobro): string
    {
        return \Carbon\Carbon::parse($cobro->fecha_vencimiento)
            ->addMonthNoOverflow()
            ->toDateString();
    }

    /**
     * Concepto descriptivo para el cobro de saldo parcial pendiente.
     * Deja claro que es un remanente de un cobro anterior.
     */
    public static function conceptoSaldoPendiente(self $cobro): string
    {
        return 'Saldo pendiente de: ' . $cobro->concepto;
    }
}
