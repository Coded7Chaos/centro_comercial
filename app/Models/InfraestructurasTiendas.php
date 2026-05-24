<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class InfraestructurasTiendas extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'numero', 'descripcion', 'telefono_referencia', 'tamano', 'cliente_id', 'id_estado'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('infraestructura');
    }

    protected $fillable = [
        'infraestructura_piso_id',
        'nombre',
        'numero',
        'descripcion',
        'telefono_referencia',
        'tamano',
        'cliente_id',
        'id_estado',
    ];

    public function piso(): BelongsTo
    {
        return $this->belongsTo(
            InfraestructurasPisos::class,
            'infraestructura_piso_id'
        );
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            Clientes::class,
            'cliente_id'
        );
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(
            EstadoTienda::class,
            'id_estado'
        );
    }
    
    public function marcas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Marcas::class,
            'infraestructuras_tiendas_marcas',
            'infraestructuras_tienda_id',
            'marca_id'
        )->withTimestamps();
    }

    public function productos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            Productos::class,
            'infraestructuras_tienda_id'
        );
    }
}
