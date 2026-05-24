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
