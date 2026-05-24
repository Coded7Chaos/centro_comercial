<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SuscripcionesTarifas extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tamano_min', 'tamano_max', 'etiqueta', 'tipo', 'precio'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('tarifas');
    }

    protected $table =
        'suscripciones_tarifas';

    protected $fillable = [
        'tamano_min',
        'tamano_max',
        'etiqueta',
        'tipo',
        'precio',
    ];

    protected $casts = [
        'tamano_min' => 'decimal:2',
        'tamano_max' => 'decimal:2',
        'precio'     => 'decimal:2',
    ];

    public static function tipos(): array
    {
        return [
            'mensual'     => 'Mensual',
            'bimestral'   => 'Bimestral',
            'trimestral'  => 'Trimestral',
            'semestral'   => 'Semestral',
            'anual'       => 'Anual',
            'semanal'     => 'Semanal',
        ];
    }

    public static function precioPara(?float $tamano, ?string $tipo): ?self
    {
        if ($tamano === null || $tipo === null) {
            return null;
        }

        return static::where('tipo', $tipo)
            ->where('tamano_min', '<=', $tamano)
            ->where('tamano_max', '>=', $tamano)
            ->orderBy('tamano_min', 'desc')
            ->first();
    }
}
