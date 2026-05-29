<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuscripcionesTarifas;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\TestCase;

class AdminTarifasCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the seeder to ensure roles and permissions are properly loaded in the test environment
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_super_admin_has_full_crud_permissions_on_tarifas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($user->can('viewAny', SuscripcionesTarifas::class));
        $this->assertTrue($user->can('view', SuscripcionesTarifas::class));
        $this->assertTrue($user->can('create', SuscripcionesTarifas::class));
        
        $tarifa = SuscripcionesTarifas::create([
            'tamano_min' => 1.0,
            'tamano_max' => 10.0,
            'etiqueta' => 'Tarifa Especial',
            'tipo' => 'mensual',
            'precio' => 100.00,
        ]);

        $this->assertTrue($user->can('update', $tarifa));
        $this->assertTrue($user->can('delete', $tarifa));
    }

    public function test_admin_has_full_crud_permissions_on_tarifas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->can('viewAny', SuscripcionesTarifas::class));
        $this->assertTrue($user->can('view', SuscripcionesTarifas::class));
        $this->assertTrue($user->can('create', SuscripcionesTarifas::class));
        
        $tarifa = SuscripcionesTarifas::create([
            'tamano_min' => 1.0,
            'tamano_max' => 10.0,
            'etiqueta' => 'Tarifa Especial',
            'tipo' => 'mensual',
            'precio' => 100.00,
        ]);

        $this->assertTrue($user->can('update', $tarifa));
        $this->assertTrue($user->can('delete', $tarifa));
    }

    public function test_client_cannot_access_any_crud_operations_on_tarifas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('cliente');

        $this->assertFalse($user->can('viewAny', SuscripcionesTarifas::class));
        $this->assertFalse($user->can('view', SuscripcionesTarifas::class));
        $this->assertFalse($user->can('create', SuscripcionesTarifas::class));

        $tarifa = SuscripcionesTarifas::create([
            'tamano_min' => 1.0,
            'tamano_max' => 10.0,
            'etiqueta' => 'Tarifa Especial',
            'tipo' => 'mensual',
            'precio' => 100.00,
        ]);

        $this->assertFalse($user->can('update', $tarifa));
        $this->assertFalse($user->can('delete', $tarifa));
    }
}
