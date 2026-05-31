<?php

namespace Database\Seeders;

use App\Models\SuscripcionesTarifas;
use Illuminate\Database\Seeder;

class SuscripcionesTarifasSeeder extends Seeder
{
    public function run(): void
    {
        $rangos = [
            ['min' => 0,    'max' => 30,    'etiqueta' => 'Pequeño',  'base' => 800],
            ['min' => 30.01,'max' => 60,    'etiqueta' => 'Mediano',  'base' => 1500],
            ['min' => 60.01,'max' => 9999,  'etiqueta' => 'Grande',   'base' => 2800],
        ];

        // Seed tamano_etiquetas and tamano_precios
        foreach ($rangos as $rango) {
            $etiqueta = \App\Models\TamanoEtiqueta::updateOrCreate(
                ['nombre' => $rango['etiqueta']],
                [
                    'desde' => $rango['min'],
                    'hasta' => $rango['max'],
                ]
            );

            \App\Models\TamanoPrecio::updateOrCreate(
                ['tamano_etiqueta_id' => $etiqueta->id],
                ['precio_mensual' => $rango['base']]
            );
        }

        // Seed descuentos_tiempo
        $descuentos = [
            ['min_meses' => 3, 'descuento' => 10.00],
            ['min_meses' => 7, 'descuento' => 15.00],
        ];

        foreach ($descuentos as $desc) {
            \App\Models\DescuentoTiempo::updateOrCreate(
                ['min_meses' => $desc['min_meses']],
                ['descuento' => $desc['descuento']]
            );
        }

        $multiplicadores = [
            'semanal'    => 0.30,
            'mensual'    => 1.00,
            'bimestral'  => 1.95,
            'trimestral' => 2.85,
            'semestral'  => 5.50,
            'anual'      => 10.50,
        ];

        foreach ($rangos as $rango) {
            foreach ($multiplicadores as $tipo => $factor) {
                SuscripcionesTarifas::updateOrCreate(
                    [
                        'tamano_min' => $rango['min'],
                        'tamano_max' => $rango['max'],
                        'tipo'       => $tipo,
                    ],
                    [
                        'etiqueta' => $rango['etiqueta'],
                        'precio'   => round($rango['base'] * $factor, 2),
                    ]
                );
            }
        }
    }
}
