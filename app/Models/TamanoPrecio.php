<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TamanoPrecio extends Model
{
    use LogsActivity;

    protected $table = 'tamano_precios';

    protected $fillable = [
        'tamano_etiqueta_id',
        'precio_mensual',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tamano_etiqueta_id', 'precio_mensual'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('tamano_precios');
    }

    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(TamanoEtiqueta::class, 'tamano_etiqueta_id');
    }
}
