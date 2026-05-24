<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Marcas extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'descripcion', 'cliente_id', 'estado'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('marcas');
    }
    
    protected $fillable = [
        'logo',
        'nombre',
        'estado',
        'cliente_id',
        'descripcion',
        'estado',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }

    public function tiendas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            InfraestructurasTiendas::class,
            'infraestructuras_tiendas_marcas',
            'marca_id',
            'infraestructuras_tienda_id'
        )->withTimestamps();
    }
}
