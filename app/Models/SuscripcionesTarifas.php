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
        'infraestructura_id',
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

    public static function calcularAlquiler(?float $tamano, ?int $meses): array
    {
        if ($tamano === null || $meses === null || $meses <= 0) {
            return [
                'etiqueta' => '—',
                'precio_mensual_base' => 0.00,
                'descuento_porcentaje' => 0.00,
                'descuento_monto_mensual' => 0.00,
                'precio_mensual_con_descuento' => 0.00,
                'precio_total_sin_descuento' => 0.00,
                'precio_total_con_descuento' => 0.00,
            ];
        }

        // 1. Encontrar etiqueta de tamaño
        $etiqueta = TamanoEtiqueta::where('desde', '<=', $tamano)
            ->where('hasta', '>=', $tamano)
            ->first();

        $etiquetaNombre = $etiqueta ? $etiqueta->nombre : 'Sin Categoría';
        
        // 2. Encontrar precio base mensual
        $precioMensualBase = 0.00;
        if ($etiqueta && $etiqueta->precio) {
            $precioMensualBase = (float) $etiqueta->precio->precio_mensual;
        }

        // 3. Encontrar descuento según meses
        // Buscamos el descuento máximo aplicable donde min_meses <= $meses
        $descuentoModel = DescuentoTiempo::where('min_meses', '<=', $meses)
            ->orderBy('min_meses', 'desc')
            ->first();

        $descuentoPorcentaje = $descuentoModel ? (float) $descuentoModel->descuento : 0.00;

        // 4. Realizar cálculos
        $descuentoMontoMensual = ($precioMensualBase * $descuentoPorcentaje) / 100;
        $precioMensualConDescuento = $precioMensualBase - $descuentoMontoMensual;
        $precioTotalSinDescuento = $precioMensualBase * $meses;
        $precioTotalConDescuento = $precioMensualConDescuento * $meses;

        return [
            'etiqueta' => $etiquetaNombre,
            'precio_mensual_base' => round($precioMensualBase, 2),
            'descuento_porcentaje' => round($descuentoPorcentaje, 2),
            'descuento_monto_mensual' => round($descuentoMontoMensual, 2),
            'precio_mensual_con_descuento' => round($precioMensualConDescuento, 2),
            'precio_total_sin_descuento' => round($precioTotalSinDescuento, 2),
            'precio_total_con_descuento' => round($precioTotalConDescuento, 2),
        ];
    }

    public static function precioPara(?float $tamano, ?string $tipo): ?object
    {
        if ($tamano === null || $tipo === null) {
            return null;
        }

        $meses = match (strtolower($tipo)) {
            'semanal' => 1,
            'mensual' => 1,
            'bimestral' => 2,
            'trimestral' => 3,
            'semestral' => 6,
            'anual' => 12,
            default => 1,
        };

        $calc = static::calcularAlquiler($tamano, $meses);

        return (object) [
            'precio' => $calc['precio_total_con_descuento'],
            'etiqueta' => $calc['etiqueta'],
        ];
    }
}
