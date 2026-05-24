<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Infraestructuras extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'pisos', 'ubicacion', 'lat', 'long'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('infraestructura');
    }

    protected $fillable = [
        'nombre',
        'pisos',
        'ubicacion',
        'lat',
        'long',
    ];

    public function pisosInfraestructura(): HasMany
    {
        return $this->hasMany(
            InfraestructurasPisos::class,
            'infraestructura_id'
        );
    }
}
