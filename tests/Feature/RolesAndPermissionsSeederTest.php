<?php

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_role_has_limited_operational_permissions(): void
    {
        $admin = Role::findByName('admin');

        $this->assertTrue($admin->hasPermissionTo('ViewAny:Role'));
        $this->assertTrue($admin->hasPermissionTo('View:Role'));
        $this->assertFalse($admin->hasPermissionTo('Create:Role'));
        $this->assertFalse($admin->hasPermissionTo('Update:Role'));
        $this->assertFalse($admin->hasPermissionTo('Delete:Role'));

        $this->assertTrue($admin->hasPermissionTo('ViewAny:User'));
        $this->assertTrue($admin->hasPermissionTo('View:User'));
        $this->assertFalse($admin->hasPermissionTo('Create:User'));
        $this->assertFalse($admin->hasPermissionTo('Update:User'));
        $this->assertFalse($admin->hasPermissionTo('Delete:User'));

        $this->assertTrue($admin->hasPermissionTo('Create:Suscripciones'));
        $this->assertFalse($admin->hasPermissionTo('Update:Suscripciones'));
        $this->assertFalse($admin->hasPermissionTo('Delete:Suscripciones'));

        $this->assertTrue($admin->hasPermissionTo('Create:SuscripcionesPagos'));
        $this->assertFalse($admin->hasPermissionTo('Update:SuscripcionesPagos'));
        $this->assertFalse($admin->hasPermissionTo('Delete:SuscripcionesPagos'));

        $this->assertTrue($admin->hasPermissionTo('ViewAny:SuscripcionesCobros'));
        $this->assertFalse($admin->hasPermissionTo('Create:SuscripcionesCobros'));
        $this->assertFalse($admin->hasPermissionTo('Update:SuscripcionesCobros'));
        $this->assertFalse($admin->hasPermissionTo('Delete:SuscripcionesCobros'));

        $this->assertTrue($admin->hasPermissionTo('ViewAny:Productos'));
        $this->assertTrue($admin->hasPermissionTo('Delete:Productos'));
        $this->assertFalse($admin->hasPermissionTo('Create:Productos'));
        $this->assertFalse($admin->hasPermissionTo('Update:Productos'));

        $this->assertTrue($admin->hasPermissionTo('View:Auditoria'));
        $this->assertFalse($admin->hasPermissionTo('View:MiEstadoDeCuenta'));
    }

    public function test_super_admin_gets_all_permissions_except_client_account_page(): void
    {
        $superAdmin = Role::findByName('super_admin');

        $this->assertTrue($superAdmin->hasPermissionTo('View:Auditoria'));
        $this->assertTrue($superAdmin->hasPermissionTo('View:TopMorososWidget'));
        $this->assertTrue($superAdmin->hasPermissionTo('Create:Role'));
        $this->assertFalse($superAdmin->hasPermissionTo('View:MiEstadoDeCuenta'));
    }

    public function test_cliente_keeps_account_page_permission(): void
    {
        $cliente = Role::findByName('cliente');

        $this->assertTrue($cliente->hasPermissionTo('View:MiEstadoDeCuenta'));
    }
}
