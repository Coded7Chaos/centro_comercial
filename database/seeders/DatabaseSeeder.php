<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            EstadosTiendasSeeder::class,
            SuscripcionesTarifasSeeder::class,
            AdministradoresSeeder::class,
            ClientesSeeder::class,
            MallDataSeeder::class,
            SuscripcionesSeeder::class,
            PagosSeeder::class,
            ProductosSeeder::class,
        ]);
    }
}
