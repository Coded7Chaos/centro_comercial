<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Clientes;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use App\Filament\Resources\Suscripciones\SuscripcionesResource;
use App\Filament\Resources\SuscripcionesCobros\SuscripcionesCobrosResource;
use App\Filament\Resources\SuscripcionesPagos\SuscripcionesPagosResource;
use App\Filament\Pages\ReporteMorosidad;
use App\Filament\Pages\BalanceSuscripciones;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AdminGlobalSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected Clientes $client;
    protected InfraestructurasTiendas $shop;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Create client user named "María Gómez" (mix of accents and uppercase)
        $userClient = User::factory()->create([
            'nombres' => 'María',
            'apellido_paterno' => 'Gómez',
        ]);
        $userClient->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $userClient->id,
            'ci' => '7777777',
            'numero_celular' => '77777777',
            'genero' => 'femenino',
        ]);

        $infra = Infraestructuras::create([
            'nombre' => 'Mall Central custom',
            'ubicacion' => 'La Paz',
            'pisos' => 1,
        ]);
        $piso = InfraestructurasPisos::create([
            'nombre' => 'Piso 1',
            'infraestructura_id' => $infra->id,
        ]);
        // Shop named "El Rinconcito de Luz"
        $this->shop = InfraestructurasTiendas::create([
            'numero' => 'Shop-999',
            'nombre' => 'El Rinconcito de Luz',
            'tamano' => '10x10',
            'id_estado' => 1,
            'infraestructura_piso_id' => $piso->id,
        ]);
    }

    public function test_suscripciones_search_by_client_name_accent_insensitive(): void
    {
        $this->actingAs($this->adminUser);

        $sub = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'tipo' => '1 mes',
            'precio' => 1000.00,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
        ]);

        // Search "maria" (lowercase, no accent) should match "María"
        $query = SuscripcionesResource::getEloquentQuery();
        // Simulate search filter
        $query->where(function ($q) {
            $q->whereHas('cliente.user', function ($sq) {
                $sq->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%maria%"]);
            });
        });

        $this->assertContains($sub->id, $query->pluck('id')->all());

        // Search "gomez" (lowercase, no accent) should match "Gómez"
        $query2 = SuscripcionesResource::getEloquentQuery();
        $query2->where(function ($q) {
            $q->whereHas('cliente.user', function ($sq) {
                $sq->whereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%gomez%"]);
            });
        });

        $this->assertContains($sub->id, $query2->pluck('id')->all());
    }

    public function test_suscripciones_search_by_tienda_name_accent_insensitive(): void
    {
        $this->actingAs($this->adminUser);

        $sub = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'tipo' => '1 mes',
            'precio' => 1000.00,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
        ]);

        // Search "rinconcito" should match "El Rinconcito de Luz"
        $query = SuscripcionesResource::getEloquentQuery();
        $query->where(function ($q) {
            $q->whereHas('infraestructurasTienda', function ($sq) {
                $sq->whereRaw("unaccent(lower(nombre)) ILIKE unaccent(lower(?))", ["%rinconcito%"]);
            });
        });

        $this->assertContains($sub->id, $query->pluck('id')->all());
    }

    public function test_reporte_morosidad_and_balance_pages_search_do_not_crash(): void
    {
        $this->actingAs($this->adminUser);

        $sub = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'tipo' => '1 mes',
            'precio' => 1000.00,
            'fecha_inicio' => now()->subMonths(2)->toDateString(),
            'fecha_fin' => now()->subMonth()->toDateString(),
        ]);

        $cobro = SuscripcionesCobros::create([
            'suscripcion_id' => $sub->id,
            'concepto' => 'Cobro Test',
            'monto' => 1000.00,
            'fecha_inicio' => now()->subMonths(2)->toDateString(),
            'fecha_vencimiento' => now()->subMonth()->toDateString(),
            'estado' => 'pendiente',
        ]);

        // 1. Test ReporteMorosidad Table with Search
        Livewire::test(ReporteMorosidad::class)
            ->set('tableSearch', 'maria')
            ->assertHasNoErrors();

        // 2. Test BalanceSuscripciones Table with Search
        Livewire::test(BalanceSuscripciones::class)
            ->set('tableSearch', 'maria')
            ->assertHasNoErrors();
    }
}
