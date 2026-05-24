<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Permisos y roles primero (los siguientes seeders los usan)
            RolesAndPermissionsSeeder::class,

            // 2. Catálogos de soporte (FKs para tiendas, tarifas)
            EstadosTiendasSeeder::class,
            SuscripcionesTarifasSeeder::class,

            // 3. Usuarios base (super_admin, admin, cliente de prueba)
            AdminUserSeeder::class,

            // 4. Datos del mall (infraestructura, pisos, tiendas, productos)
            MallDataSeeder::class,

            // 5. Demo de contratos, cobros y pagos para auditoría / morosidad
            SuscripcionesDemoSeeder::class,
        ]);
    }
}
