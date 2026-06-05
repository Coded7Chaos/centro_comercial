<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class InfraestructurasTiendas extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'numero', 'descripcion', 'telefono_referencia', 'tamano', 'cliente_id', 'id_estado', 'vitrina_1', 'vitrina_2', 'vitrina_3'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('infraestructura');
    }

    protected $fillable = [
        'infraestructura_piso_id',
        'nombre',
        'numero',
        'descripcion',
        'telefono_referencia',
        'tamano',
        'cliente_id',
        'id_estado',
        'vitrina_1',
        'vitrina_2',
        'vitrina_3',
    ];

    public function piso(): BelongsTo
    {
        return $this->belongsTo(
            InfraestructurasPisos::class,
            'infraestructura_piso_id'
        );
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            Clientes::class,
            'cliente_id'
        );
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(
            EstadoTienda::class,
            'id_estado'
        );
    }
    
    public function marcas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Marcas::class,
            'infraestructuras_tiendas_marcas',
            'infraestructuras_tienda_id',
            'marca_id'
        )->withTimestamps();
    }

    public function getMarcaAttribute()
    {
        if ($this->cliente_id) {
            $matchingBrand = $this->marcas->firstWhere('cliente_id', $this->cliente_id);
            if ($matchingBrand) {
                return $matchingBrand;
            }
        }
        return $this->marcas->first();
    }

    public function productos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            Productos::class,
            'infraestructuras_tienda_id'
        );
    }

    public function getPrecioMensualReferencial(): float
    {
        // Try to get price of latest subscription
        $latestSub = Suscripciones::where('infraestructuras_tienda_id', $this->id)
            ->latest('fecha_inicio')
            ->first();
            
        if ($latestSub) {
            $precio = (float) $latestSub->precio;
            $tipo = strtolower($latestSub->type ?? $latestSub->tipo);
            if (preg_match('/(\d+)\s*mes/', $tipo, $matches)) {
                $meses = (int) $matches[1];
                return $precio / max(1, $meses);
            }
            if (preg_match('/(\d+)\s*año/', $tipo, $matches)) {
                $anos = (int) $matches[1];
                return $precio / max(1, $anos * 12);
            }
            switch ($tipo) {
                case 'semanal':
                    return $precio * 4;
                case 'mensual':
                    return $precio;
                case 'bimestral':
                    return $precio / 2;
                case 'trimestral':
                    return $precio / 3;
                case 'semestral':
                    return $precio / 6;
                case 'anual':
                    return $precio / 12;
            }
        }
        
        // Otherwise look up in SuscripcionesTarifas
        $tarifa = \App\Models\SuscripcionesTarifas::precioPara((float)$this->tamano, 'mensual');
        if ($tarifa) {
            return (float) $tarifa->precio;
        }
        
        return 150.00; // default reference monthly price
    }

    public function getFechaLibreDesde(): \Carbon\Carbon
    {
        $latestSub = Suscripciones::where('infraestructuras_tienda_id', $this->id)
            ->latest('fecha_fin')
            ->first();
            
        if ($latestSub) {
            return \Carbon\Carbon::parse($latestSub->fecha_fin)->addDay();
        }
        
        return $this->created_at ? \Carbon\Carbon::parse($this->created_at) : now()->subDays(30);
    }

    public function getDiasLibre(): int
    {
        $fechaLibre = $this->getFechaLibreDesde();
        if ($fechaLibre->isFuture()) {
            return 0;
        }
        return max(0, $fechaLibre->diffInDays(now()));
    }

    public function getCostoOportunidad(): float
    {
        $dias = $this->getDiasLibre();
        if ($dias <= 0) {
            return 0.0;
        }
        $mensual = $this->getPrecioMensualReferencial();
        return round(($mensual / 30.0) * $dias, 2);
    }

    protected static function booted(): void
    {
        static::deleting(function (InfraestructurasTiendas $tienda) {
            // Detach brands
            $tienda->marcas()->detach();

            // Nullify related subscriptions
            \App\Models\Suscripciones::where('infraestructuras_tienda_id', $tienda->id)
                ->update(['infraestructuras_tienda_id' => null]);
        });
    }
}

