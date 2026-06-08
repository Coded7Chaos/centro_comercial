<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clientes;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesTarifas;
use App\Models\EstadoTienda;
use App\Filament\Widgets\CostoOportunidadVacanciaChart;
use App\Filament\Widgets\PerdidasMensualesVacanciaChart;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\EstadosTiendasSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MapaOcupacionConsolidatedTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $superAdmin;
    protected $clientUser;
    protected $infra;
    protected $piso;
    protected $tienda;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(EstadosTiendasSeeder::class);

        // Setup users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->clientUser = User::factory()->create();
        $this->clientUser->assignRole('cliente');

        // Setup infrastructure
        $this->infra = Infraestructuras::first() ?? Infraestructuras::create([
            'nombre' => 'Mall Gran Test',
            'ubicacion' => 'Test Town',
            'pisos' => 3,
        ]);

        $this->piso = InfraestructurasPisos::where('infraestructura_id', $this->infra->id)->first()
            ?? InfraestructurasPisos::create([
                'nombre' => 'Piso 1',
                'infraestructura_id' => $this->infra->id,
            ]);

        $disponibleEstado = EstadoTienda::where('estado', 'Disponible')->first();

        $this->tienda = InfraestructurasTiendas::create([
            'numero' => 999,
            'tamano' => '10.5',
            'id_estado' => $disponibleEstado->id,
            'infraestructura_piso_id' => $this->piso->id,
        ]);
    }

    public function test_tienda_helpers_calculates_vacancy_days_and_cost(): void
    {
        // 1. New shop with no subscription
        // It has been vacant since its creation (which is now)
        $this->assertEquals(0, $this->tienda->getDiasLibre());
        $this->assertEquals(0.0, $this->tienda->getCostoOportunidad());

        // 2. Set created_at to 10 days ago to simulate vacancy duration
        $this->tienda->created_at = now()->subDays(10);
        $this->tienda->save();

        // Check vacancy duration
        $this->assertEquals(10, $this->tienda->getDiasLibre());

        // Opportunity cost: dynamically calculated from the referential rate (either fallback 150 or a pre-seeded rate)
        $refRate = $this->tienda->getPrecioMensualReferencial();
        $expectedCost = round(($refRate / 30.0) * 10, 2);
        $this->assertEquals($expectedCost, $this->tienda->getCostoOportunidad());

        // Clear new pricing tables inside transaction to avoid matching pre-seeded values
        \App\Models\TamanoPrecio::query()->delete();
        \App\Models\TamanoEtiqueta::query()->delete();

        // 3. Create a rate that overrides it (e.g. for size 10.5, price 300.00)
        $etiqueta = \App\Models\TamanoEtiqueta::create([
            'nombre' => 'Test rate overriding',
            'desde' => 10.0,
            'hasta' => 11.0,
        ]);
        \App\Models\TamanoPrecio::create([
            'tamano_etiqueta_id' => $etiqueta->id,
            'precio_mensual' => 300.00,
        ]);

        // Monthly rate lookup for size 10.5 should return 300.00
        // Opportunity cost: 300.00 / 30 * 10 = 100.0
        $this->assertEquals(300.00, $this->tienda->getPrecioMensualReferencial());
        $this->assertEquals(100.0, $this->tienda->getCostoOportunidad());
    }

    public function test_tienda_helpers_uses_latest_subscription_price_for_cost(): void
    {
        // Create a client
        $client = Clientes::create([
            'user_id' => $this->clientUser->id,
            'ci' => '9999999',
            'numero_celular' => '79999999',
            'genero' => 'masculino',
        ]);

        // Create a previous subscription that ended 5 days ago
        $sub = Suscripciones::create([
            'cliente_id' => $client->id,
            'infraestructuras_tienda_id' => $this->tienda->id,
            'tipo' => 'trimestral',
            'precio' => 900.00, // 300.00/month equivalent
            'fecha_inicio' => now()->subDays(95),
            'fecha_fin' => now()->subDays(5),
        ]);

        // Vacant since: fecha_fin + 1 day = 4 days ago
        $expectedDate = now()->subDays(4)->startOfDay();
        $this->assertEquals($expectedDate->format('Y-m-d'), $this->tienda->getFechaLibreDesde()->format('Y-m-d'));
        
        // Days vacant: 4 days
        $this->assertEquals(4, $this->tienda->getDiasLibre());

        // Monthly price referencial: trimestral 900.00 => 300.00/month
        $this->assertEquals(300.00, $this->tienda->getPrecioMensualReferencial());

        // Cost of opportunity: 300.00 / 30 * 4 = 40.0
        $this->assertEquals(40.0, $this->tienda->getCostoOportunidad());
    }

    public function test_mapa_ocupacion_page_access_permissions(): void
    {
        $this->assertTrue($this->admin->can('View:MapaOcupacion'));
        $this->assertTrue($this->superAdmin->can('View:MapaOcupacion'));
        $this->assertFalse($this->clientUser->can('View:MapaOcupacion'));
    }

    public function test_costo_oportunidad_vacancia_chart_access_permissions(): void
    {
        $this->assertTrue(CostoOportunidadVacanciaChart::canView() === false); // since no user authenticated in static check yet
        
        $this->actingAs($this->admin);
        $this->assertTrue($this->admin->can('View:CostoOportunidadVacanciaChart'));
        $this->assertTrue(CostoOportunidadVacanciaChart::canView());

        $this->actingAs($this->clientUser);
        $this->assertFalse($this->clientUser->can('View:CostoOportunidadVacanciaChart'));
        $this->assertFalse(CostoOportunidadVacanciaChart::canView());
    }

    public function test_vacancy_chart_accumulates_from_first_vacancy_date(): void
    {
        $openingDate = now()->subDays(45);

        $infra = Infraestructuras::create([
            'nombre' => 'Mall Vacancia Histórica',
            'ubicacion' => 'Test Town',
            'pisos' => 1,
            'created_at' => $openingDate,
            'updated_at' => $openingDate,
        ]);

        $piso = InfraestructurasPisos::create([
            'nombre' => 'Piso histórico',
            'infraestructura_id' => $infra->id,
            'created_at' => $openingDate,
            'updated_at' => $openingDate,
        ]);

        $disponibleEstado = EstadoTienda::where('estado', 'Disponible')->first();

        $tienda = InfraestructurasTiendas::create([
            'numero' => 'H-101',
            'tamano' => '20',
            'id_estado' => $disponibleEstado->id,
            'infraestructura_piso_id' => $piso->id,
            'created_at' => $openingDate,
            'updated_at' => $openingDate,
        ]);
        DB::table('infraestructuras_tiendas')
            ->where('id', $tienda->id)
            ->update([
                'created_at' => $openingDate,
                'updated_at' => $openingDate,
            ]);

        $chart = new CostoOportunidadVacanciaChart();
        $chart->activeInfraId = $infra->id;

        $method = new \ReflectionMethod(CostoOportunidadVacanciaChart::class, 'getData');
        $method->setAccessible(true);
        $data = $method->invoke($chart);

        $this->assertSame($openingDate->translatedFormat('M Y'), $data['labels'][0]);
        $this->assertLessThanOrEqual(3, count($data['labels']));
        $this->assertGreaterThan(0, end($data['datasets'][0]['data']));
        $this->assertGreaterThan(0, end($data['datasets'][1]['data']));
    }

    public function test_monthly_vacancy_loss_chart_shows_non_accumulated_monthly_values(): void
    {
        $infra = Infraestructuras::create([
            'nombre' => 'Mall Perdidas Mensuales',
            'ubicacion' => 'La Paz',
            'pisos' => 1,
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'Piso 1',
        ]);

        $disponibleEstado = EstadoTienda::firstOrCreate(['estado' => 'Disponible']);
        $openingDate = now()->startOfMonth()->subMonths(2)->toDateString();

        $tienda = InfraestructurasTiendas::create([
            'numero' => 'M-101',
            'tamano' => '20',
            'id_estado' => $disponibleEstado->id,
            'infraestructura_piso_id' => $piso->id,
            'created_at' => $openingDate,
            'updated_at' => $openingDate,
        ]);

        DB::table('infraestructuras_tiendas')
            ->where('id', $tienda->id)
            ->update([
                'created_at' => $openingDate,
                'updated_at' => $openingDate,
            ]);

        $chart = new PerdidasMensualesVacanciaChart();
        $chart->activeInfraId = $infra->id;

        $method = new \ReflectionMethod(PerdidasMensualesVacanciaChart::class, 'getData');
        $method->setAccessible(true);
        $data = $method->invoke($chart);

        $this->assertGreaterThanOrEqual(2, count($data['labels']));
        $this->assertCount(count($data['labels']), $data['datasets'][0]['data']);
        $this->assertGreaterThan(0, max($data['datasets'][0]['data']));
        $this->assertLessThan(
            array_sum($data['datasets'][0]['data']),
            end($data['datasets'][0]['data'])
        );
    }
}
