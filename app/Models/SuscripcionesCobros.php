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
    ];

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
}
