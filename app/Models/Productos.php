<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Productos extends Model
{
    use LogsActivity;

    protected $table = 'productos';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'descripcion', 'precio', 'categoria_id', 'marca_id', 'estado'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('productos');
    }

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria_id',
        'subcategoria_id',
        'precio',
        'marca_id',
        'estado',
        'infraestructuras_tienda_id',
    ];

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marcas::class, 'marca_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categorias::class, 'categoria_id');
    }

    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo(Categorias::class, 'subcategoria_id');
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ProductosImagenes::class, 'producto_id');
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(InfraestructurasTiendas::class, 'infraestructuras_tienda_id');
    }
}
