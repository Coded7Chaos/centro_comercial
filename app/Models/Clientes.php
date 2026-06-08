<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Clientes extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['ci', 'numero_celular', 'correo_secundario', 'user_id', 'genero'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('clientes');
    }

    protected $fillable = [
        'ci',
        'genero',
        'numero_celular',
        'codigo_pais',
        'correo_secundario',
        'user_id',
        'foto',
    ];

    // RELACIÓN CON USER
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ClientesDocumentos::class, 'cliente_id');
    }

    public function marcas(): HasMany
    {
        return $this->hasMany(Marcas::class, 'cliente_id');
    }

    public function tiendas(): HasMany
    {
        return $this->hasMany(InfraestructurasTiendas::class, 'cliente_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        if ($this->user) {
            return "{$this->user->nombres} {$this->user->apellido_paterno}";
        }

        return "Cliente #{$this->id}";
    }

    protected static function booted(): void
    {
        static::deleting(function (Clientes $cliente) {
            if (! $cliente->isForceDeleting() && $cliente->user && ! $cliente->user->trashed()) {
                $cliente->user->delete();
            }
        });

        static::restoring(function (Clientes $cliente) {
            if ($cliente->user && $cliente->user->trashed()) {
                $cliente->user->restore();
            }
        });
    }
}
