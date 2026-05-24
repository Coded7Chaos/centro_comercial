<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class InfraestructurasPisos extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['infraestructura_id', 'nombre', 'cantidad_tiendas', 'numero', 'estado'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('infraestructura');
    }

    protected $table = 'infraestructuras_pisos';

    protected $fillable = [
        'infraestructura_id',
        'nombre',
        'cantidad_tiendas',
        'numero',
        'cliente_id',
        'marca_id',
        'estado',
    ];

    public function infraestructura(): BelongsTo
    {
        return $this->belongsTo(
            Infraestructuras::class,
            'infraestructura_id'
        );
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            Clientes::class,
            'cliente_id'
        );
    }

    public function tiendas(): HasMany
    {
        return $this->hasMany(
            InfraestructurasTiendas::class,
            'infraestructura_piso_id'
        );
    }


}
