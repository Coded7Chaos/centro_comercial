<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Categorias extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'descripcion', 'estado', 'tipo', 'categoria_padre_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('categorias');
    }

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'tipo',
        'categoria_padre_id',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Productos::class, 'categoria_id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Categorias::class, 'categoria_padre_id');
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(Categorias::class, 'categoria_padre_id');
    }
}