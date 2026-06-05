<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TamanoEtiqueta;
use App\Models\TamanoPrecio;
use App\Models\DescuentoTiempo;
use App\Models\SuscripcionesTarifas;
use App\Filament\Pages\SimuladorAlquiler;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class SimuladorAlquilerTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\RequiereInfraestructuraActiva::class);

        // Seed roles & permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create admin user with View:SimuladorAlquiler permission
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Create a user without that permission
        $this->clientUser = User::factory()->create();
        $this->clientUser->assignRole('cliente');

        // Clean tables to ensure tests run in isolation
        TamanoPrecio::query()->delete();
        TamanoEtiqueta::query()->delete();
        DescuentoTiempo::query()->delete();

        // Setup size categories and pricing
        $etiquetaP = TamanoEtiqueta::create([
            'nombre' => 'Pequeño Custom',
            'desde' => 0.00,
            'hasta' => 30.00,
        ]);
        TamanoPrecio::create([
            'tamano_etiqueta_id' => $etiquetaP->id,
            'precio_mensual' => 800.00,
        ]);

        // Setup discounts
        // >= 3 months -> 10%
        DescuentoTiempo::create(['min_meses' => 3, 'descuento' => 10.00]);
        // >= 12 months -> 15%
        DescuentoTiempo::create(['min_meses' => 12, 'descuento' => 15.00]);
    }

    public function test_admin_can_access_simulador_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/simulador-alquiler');

        $response->assertStatus(200);
    }

    public function test_client_cannot_access_simulador_page(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/admin/simulador-alquiler');

        $response->assertStatus(403);
    }

    public function test_simulador_initializes_with_defaults(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SimuladorAlquiler::class)
            ->assertSet('data.duracion_valor', 1)
            ->assertSet('data.duracion_unidad', 'meses');
    }

    public function test_simulador_calculates_cotizacion_correctly_without_discount(): void
    {
        $this->actingAs($this->adminUser);

        // 1 month contract (0% discount) for size 25.0 (Base price: 800)
        Livewire::test(SimuladorAlquiler::class)
            ->set('data.tamano', 25)
            ->set('data.duracion_valor', 1)
            ->set('data.duracion_unidad', 'meses')
            ->call('calcularCotizacion')
            ->assertSet('data.etiqueta', 'Pequeño Custom')
            ->assertSet('data.descuento_porcentaje', '0.00')
            ->assertSet('data.pago_mensual_estimado', '800.00')
            ->assertSet('data.garantia', '800.00')
            ->assertSet('data.cotizacion', '1600.00');
    }

    public function test_simulador_calculates_cotizacion_correctly_with_monthly_discount(): void
    {
        $this->actingAs($this->adminUser);

        // 3 months contract (10% discount) for size 25.0
        // Base monthly: 800. Discount monthly: 80. Con descuento: 720. Total con descuento: 2160. Guarantee: 720. Total: 2880.
        Livewire::test(SimuladorAlquiler::class)
            ->set('data.tamano', 25)
            ->set('data.duracion_valor', 3)
            ->set('data.duracion_unidad', 'meses')
            ->set('data.garantia', null)
            ->call('calcularCotizacion')
            ->assertSet('data.etiqueta', 'Pequeño Custom')
            ->assertSet('data.descuento_porcentaje', '10.00')
            ->assertSet('data.pago_mensual_estimado', '720.00')
            ->assertSet('data.garantia', '720.00')
            ->assertSet('data.cotizacion', '2880.00');
    }

    public function test_simulador_calculates_cotizacion_correctly_with_yearly_discount(): void
    {
        $this->actingAs($this->adminUser);

        // 1 year contract = 12 months (15% discount) for size 25.0
        // Base monthly: 800. Discount monthly: 120. Con descuento: 680. Total con descuento: 8160. Guarantee: 680. Total: 8840.
        Livewire::test(SimuladorAlquiler::class)
            ->set('data.tamano', 25)
            ->set('data.duracion_valor', 1)
            ->set('data.duracion_unidad', 'años')
            ->set('data.garantia', null)
            ->call('calcularCotizacion')
            ->assertSet('data.etiqueta', 'Pequeño Custom')
            ->assertSet('data.descuento_porcentaje', '15.00')
            ->assertSet('data.pago_mensual_estimado', '680.00')
            ->assertSet('data.garantia', '680.00')
            ->assertSet('data.cotizacion', '8840.00');
    }

    public function test_simulador_updates_garantia_when_monthly_payment_changes(): void
    {
        $this->actingAs($this->adminUser);

        // 1 month contract for size 25.0 -> base price 800, guarantee should be 800
        $test = Livewire::test(SimuladorAlquiler::class)
            ->set('data.tamano', 25)
            ->set('data.duracion_valor', 1)
            ->set('data.duracion_unidad', 'meses')
            ->call('calcularCotizacion')
            ->assertSet('data.garantia', '800.00');

        // Now change duration to 3 months (monthly payment becomes 720.00) -> guarantee should update to 720.00
        $test->set('data.duracion_valor', 3)
            ->call('calcularCotizacion')
            ->assertSet('data.garantia', '720.00');
    }
}
