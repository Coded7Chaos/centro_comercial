<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DescuentoTiempo extends Model
{
    use LogsActivity;

    protected $table = 'descuentos_tiempo';

    protected $fillable = [
        'min_meses',
        'descuento',
    ];

    protected $casts = [
        'min_meses' => 'integer',
        'descuento' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['min_meses', 'descuento'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('descuentos_tiempo');
    }
}
