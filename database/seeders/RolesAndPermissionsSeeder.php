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
            'CostoOportunidadVacanciaChart',
            'PerdidasMensualesVacanciaChart',
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
        $superAdmin->syncPermissions(
            Permission::query()
                ->where('name', '!=', 'View:MiEstadoDeCuenta')
                ->get()
        );

        // --- Admin ---
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $allActionsFor = fn (string $resource): array => array_map(
            fn (string $action): string => "{$action}:{$resource}",
            $actions
        );
        $viewOnlyFor = fn (string $resource): array => [
            "ViewAny:{$resource}",
            "View:{$resource}",
        ];

        $adminPermissionNames = collect()
            ->merge($viewOnlyFor('Role'))
            ->merge($viewOnlyFor('User'))
            ->merge([
                'ViewAny:Suscripciones',
                'View:Suscripciones',
                'Create:Suscripciones',
                'ViewAny:SuscripcionesPagos',
                'View:SuscripcionesPagos',
                'Create:SuscripcionesPagos',
                'ViewAny:SuscripcionesCobros',
                'View:SuscripcionesCobros',
                'ViewAny:Productos',
                'View:Productos',
                'Delete:Productos',
                'DeleteAny:Productos',
            ])
            ->merge($allActionsFor('Categorias'))
            ->merge($allActionsFor('Clientes'))
            ->merge($allActionsFor('ClientesDocumentos'))
            ->merge($allActionsFor('Infraestructuras'))
            ->merge($allActionsFor('InfraestructurasPisos'))
            ->merge($allActionsFor('InfraestructurasTiendas'))
            ->merge($allActionsFor('Tiendas'))
            ->merge($allActionsFor('Marcas'))
            ->merge($allActionsFor('SuscripcionesTarifas'))
            ->merge([
                'View:BalanceSuscripciones',
                'View:MapaOcupacion',
                'View:ReporteMorosidad',
                'View:SimuladorAlquiler',
                'View:Auditoria',
                'View:IngresosMensualesChart',
                'View:StatsOverview',
                'View:CobrosPorEstadoChart',
                'View:OcupacionPorPisoChart',
                'View:MetodoPagoChart',
                'View:CostoOportunidadVacanciaChart',
                'View:PerdidasMensualesVacanciaChart',
            ])
            ->unique()
            ->values();

        $admin->syncPermissions(
            Permission::whereIn('name', $adminPermissionNames)->get()
        );

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
