<?php

namespace Database\Seeders;

use App\Models\InfraestructurasTiendas;
use App\Models\EstadoTienda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RetroactiveVacancySeeder extends Seeder
{
    public function run(): void
    {
        $disponible = EstadoTienda::where('estado', 'Disponible')->first();
        
        if (!$disponible) {
            $this->command->warn('Estado "Disponible" no encontrado.');
            return;
        }

        $tiendas = InfraestructurasTiendas::where('id_estado', $disponible->id)->get();

        foreach ($tiendas as $tienda) {
            // Asignar una fecha aleatoria entre hace 10 y 60 días
            $diasAtras = rand(10, 60);
            $fechaAleatoria = Carbon::now()->subDays($diasAtras);
            
            // Usamos update para evitar disparar observadores si no es necesario,
            // aunque created_at suele ser manejado por Eloquent.
            $tienda->timestamps = false; // Desactivar timestamps para forzar la fecha
            $tienda->created_at = $fechaAleatoria;
            $tienda->save();
        }

        $this->command->info("Se han actualizado {$tiendas->count()} tiendas con fechas de vacancia retroactivas.");
    }
}
