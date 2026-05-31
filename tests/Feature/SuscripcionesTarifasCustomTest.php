<?php

namespace Tests\Feature;

use App\Models\Clientes;
use App\Models\DescuentoTiempo;
use App\Models\InfraestructurasTiendas;
use App\Models\SuscripcionesTarifas;
use App\Models\TamanoEtiqueta;
use App\Models\TamanoPrecio;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SuscripcionesTarifasCustomTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $shop;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Setup infrastructure
        $infra = \App\Models\Infraestructuras::create([
            'nombre' => 'Mall Custom Test',
            'ubicacion' => 'Test Location',
            'pisos' => 3,
        ]);
        $piso = \App\Models\InfraestructurasPisos::create([
            'nombre' => 'Piso Custom Test',
            'infraestructura_id' => $infra->id,
        ]);
        $this->shop = InfraestructurasTiendas::create([
            'numero' => 988,
            'tamano' => '25.0',
            'id_estado' => 1,
            'infraestructura_piso_id' => $piso->id,
        ]);

        // Clean tables to ensure tests run in isolation
        TamanoPrecio::query()->delete();
        TamanoEtiqueta::query()->delete();
        DescuentoTiempo::query()->delete();

        // Setup sizes & pricing
        // Category 1: 0 - 30 m2 -> 800.00
        $etiquetaP = TamanoEtiqueta::create([
            'nombre' => 'Pequeño Custom',
            'desde' => 0.00,
            'hasta' => 30.00,
        ]);
        TamanoPrecio::create([
            'tamano_etiqueta_id' => $etiquetaP->id,
            'precio_mensual' => 800.00,
        ]);

        // Category 2: 30.01 - 60 m2 -> 1500.00
        $etiquetaM = TamanoEtiqueta::create([
            'nombre' => 'Mediano Custom',
            'desde' => 30.01,
            'hasta' => 60.00,
        ]);
        TamanoPrecio::create([
            'tamano_etiqueta_id' => $etiquetaM->id,
            'precio_mensual' => 1500.00,
        ]);

        // Discounts
        // >= 3 months -> 10%
        DescuentoTiempo::create(['min_meses' => 3, 'descuento' => 10.00]);
        // >= 7 months -> 15%
        DescuentoTiempo::create(['min_meses' => 7, 'descuento' => 15.00]);
    }

    public function test_calcular_alquiler_logic(): void
    {
        // 1. One month contract (0% discount) for size 25.0 (Category: Pequeño Custom, Base price: 800)
        $calc1 = SuscripcionesTarifas::calcularAlquiler(25.0, 1);
        $this->assertEquals('Pequeño Custom', $calc1['etiqueta']);
        $this->assertEquals(800.00, $calc1['precio_mensual_base']);
        $this->assertEquals(0.00, $calc1['descuento_porcentaje']);
        $this->assertEquals(800.00, $calc1['precio_mensual_con_descuento']);
        $this->assertEquals(800.00, $calc1['precio_total_con_descuento']);

        // 2. 3 months contract (10% discount) for size 25.0
        // Price should be: 800 base -> 10% discount = 80 per month.
        // Monthly: 720. Total: 2160.
        $calc3 = SuscripcionesTarifas::calcularAlquiler(25.0, 3);
        $this->assertEquals(800.00, $calc3['precio_mensual_base']);
        $this->assertEquals(10.00, $calc3['descuento_porcentaje']);
        $this->assertEquals(80.00, $calc3['descuento_monto_mensual']);
        $this->assertEquals(720.00, $calc3['precio_mensual_con_descuento']);
        $this->assertEquals(2160.00, $calc3['precio_total_con_descuento']);

        // 3. 12 months contract (15% discount) for size 25.0
        // Price should be: 800 base -> 15% discount = 120 per month.
        // Monthly: 680. Total: 8160.
        $calc12 = SuscripcionesTarifas::calcularAlquiler(25.0, 12);
        $this->assertEquals(15.00, $calc12['descuento_porcentaje']);
        $this->assertEquals(680.00, $calc12['precio_mensual_con_descuento']);
        $this->assertEquals(8160.00, $calc12['precio_total_con_descuento']);

        // 4. Large shop (size 40.0) for 7 months (15% discount)
        // Price should be: 1500 base -> 15% discount = 225 per month.
        // Monthly: 1275. Total: 8925.
        $calcLarge = SuscripcionesTarifas::calcularAlquiler(40.0, 7);
        $this->assertEquals('Mediano Custom', $calcLarge['etiqueta']);
        $this->assertEquals(1500.00, $calcLarge['precio_mensual_base']);
        $this->assertEquals(15.00, $calcLarge['descuento_porcentaje']);
        $this->assertEquals(1275.00, $calcLarge['precio_mensual_con_descuento']);
        $this->assertEquals(8925.00, $calcLarge['precio_total_con_descuento']);
    }

    public function test_tienda_precio_endpoint_returns_discount_calculations(): void
    {
        // 1 month
        $response1 = $this->actingAs($this->adminUser)
            ->get("/admin/suscripciones-custom/tienda-precio/{$this->shop->id}?duracion_valor=1&duracion_unidad=meses");

        $response1->assertStatus(200);
        $response1->assertJson([
            'id' => $this->shop->id,
            'numero' => $this->shop->numero,
            'tamano' => 25.0,
            'precio_mensual_base' => 800.00,
            'descuento_porcentaje' => 0.00,
            'precio_mensual_con_descuento' => 800.00,
            'precio_total_con_descuento' => 800.00,
            'pago_inicial' => 800.00,
            'etiqueta' => 'Pequeño Custom',
        ]);

        // 3 months
        $response3 = $this->actingAs($this->adminUser)
            ->get("/admin/suscripciones-custom/tienda-precio/{$this->shop->id}?duracion_valor=3&duracion_unidad=meses");

        $response3->assertStatus(200);
        $response3->assertJson([
            'precio_mensual_base' => 800.00,
            'descuento_porcentaje' => 10.00,
            'precio_mensual_con_descuento' => 720.00,
            'precio_total_con_descuento' => 2160.00,
            'pago_inicial' => 1440.00, // 2 months rent equivalent (first month + deposit)
        ]);
    }
}
