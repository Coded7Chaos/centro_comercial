<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TamanoEtiqueta extends Model
{
    use LogsActivity;

    protected $table = 'tamano_etiquetas';

    protected $fillable = [
        'nombre',
        'desde',
        'hasta',
    ];

    protected $casts = [
        'desde' => 'decimal:2',
        'hasta' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'desde', 'hasta'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('tamano_etiquetas');
    }

    public function precio(): HasOne
    {
        return $this->hasOne(TamanoPrecio::class, 'tamano_etiqueta_id');
    }
}
