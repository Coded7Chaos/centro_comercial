<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoTienda;

class EstadosTiendasSeeder extends Seeder {
    public function run(): void
    {
        //Creamos los primeros estados de tiendas, basandonos en estados de alquiler/disponibilidad
        $estados = [['estado' => 'Disponible'],
                    ['estado' => 'Alquilada'],
                    ['estado' => 'En mantenimiento']
        ];

        foreach($estados as $estado){
            EstadoTienda::firstOrCreate($estado);
        }
    }
}
