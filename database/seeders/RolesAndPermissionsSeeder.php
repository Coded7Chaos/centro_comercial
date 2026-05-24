<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Resources
        $resources = [
            'Categorias',
            'Clientes',
            'ClientesDocumentos',
            'Infraestructuras',
            'InfraestructurasPisos',
            'InfraestructurasTiendas',
            'Marcas',
            'Productos',
            'ProductosImagenes',
            'Suscripciones',
            'SuscripcionesCobros',
            'SuscripcionesPagos',
            'SuscripcionesTarifas',
            'Tiendas',
            'User',
            'Role'
        ];

        $actions = [
            'ViewAny',
            'View',
            'Create',
            'Update',
            'Delete',
            'DeleteAny',
            'Restore',
            'ForceDelete',
            'ForceDeleteAny',
            'RestoreAny',
            'Replicate',
            'Reorder'
        ];

        // Pages
        $pages = [
            'BalanceSuscripciones',
            'DirectorioInterno',
            'GeneradorCobros',
            'MapaOcupacion',
            'MiEstadoDeCuenta',
            'ReporteMorosidad',
            'SimuladorAlquiler',
            'Auditoria',
        ];

        // Widgets
        $widgets = [
            'IngresosMensualesChart',
            'StatsOverview',
            'CobrosPorEstadoChart',
            'OcupacionPorPisoChart',
            'MetodoPagoChart',
            'TopMorososWidget',
        ];

        // Create Permissions
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}:{$resource}", 'guard_name' => 'web']);
            }
        }

        foreach ($pages as $page) {
            Permission::firstOrCreate(['name' => "View:{$page}", 'guard_name' => 'web']);
        }

        foreach ($widgets as $widget) {
            Permission::firstOrCreate(['name' => "View:{$widget}", 'guard_name' => 'web']);
        }

        // --- Super Admin ---
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // --- Admin ---
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::all()->reject(function ($permission) {
            return in_array($permission->name, [
                // No puede modificar cobros
                'Update:SuscripcionesCobros',
                'Delete:SuscripcionesCobros',
                'DeleteAny:SuscripcionesCobros',
                'ForceDelete:SuscripcionesCobros',
                'ForceDeleteAny:SuscripcionesCobros',
                // No puede modificar tarifas
                'Create:SuscripcionesTarifas',
                'Update:SuscripcionesTarifas',
                'Delete:SuscripcionesTarifas',
                // No accede al widget de top morosos (lista personal)
                'View:TopMorososWidget',
                // No accede a la auditoría del sistema
                'View:Auditoria',
                // Restricciones de usuarios: El usuario dijo que solo puede crear Clientes.
                // Mantendremos Create:User pero luego en la lógica de negocio o Policy
                // controlaremos que no pueda asignar roles superiores.
            ]);
        });
        $admin->syncPermissions($adminPermissions);

        // --- Cliente ---
        $cliente = Role::firstOrCreate(['name' => 'cliente', 'guard_name' => 'web']);
        $clientePermissions = [
            'ViewAny:Productos',
            'View:Productos',
            'Create:Productos',
            'Update:Productos',
            'Delete:Productos',
            'ViewAny:Marcas',
            'View:Marcas',
            'Create:Marcas',
            'View:MiEstadoDeCuenta'
        ];
        $cliente->syncPermissions($clientePermissions);
    }
}
