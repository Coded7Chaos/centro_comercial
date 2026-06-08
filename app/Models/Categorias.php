<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Categorias extends Model
{
    use LogsActivity;

    protected static function booted(): void
    {
        static::deleting(function (Categorias $categoria): void {
            Productos::where('categoria_id', $categoria->id)->update(['categoria_id' => null]);

            if ($categoria->categoria_padre_id === null) {
                $subcategoryIds = $categoria->subcategorias()->pluck('id');

                if ($subcategoryIds->isNotEmpty()) {
                    Productos::whereIn('categoria_id', $subcategoryIds)->update(['categoria_id' => null]);
                    $categoria->subcategorias()->delete();
                }
            }
        });
    }

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
        'infraestructura_id',
        'cliente_id',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Productos::class, 'categoria_id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Categorias::class, 'categoria_padre_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(Categorias::class, 'categoria_padre_id');
    }
}
