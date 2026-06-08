<?php

namespace Tests\Feature;

use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use App\Models\User;
use App\Services\ExpiredSubscriptionsService;
use App\Filament\Pages\BalanceSuscripciones;
use App\Filament\Pages\ReporteMorosidad;
use App\Filament\Widgets\TopMorososWidget;
use App\Filament\Resources\Suscripciones\Tables\SuscripcionesTable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SoftDeletedClientFinancialAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected Clientes $client;
    protected InfraestructurasTiendas $shop;
    protected Suscripciones $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles/permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Client user and client
        $userClient = User::factory()->create([
            'nombres' => 'Juan',
            'apellido_paterno' => 'Perez',
        ]);
        $userClient->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $userClient->id,
            'ci' => '1234567',
            'numero_celular' => '70012345',
            'genero' => 'masculino',
        ]);

        // Infra, Piso and Shop
        $infra = Infraestructuras::create([
            'nombre' => 'Mall Test',
            'ubicacion' => 'Santa Cruz',
            'pisos' => 1,
        ]);
        $piso = InfraestructurasPisos::create([
            'nombre' => 'Piso 1',
            'infraestructura_id' => $infra->id,
        ]);
        EstadoTienda::firstOrCreate(['estado' => 'Disponible']);
        $estadoAlquilada = EstadoTienda::firstOrCreate(['estado' => 'Alquilada']);

        $this->shop = InfraestructurasTiendas::create([
            'numero' => 101,
            'nombre' => 'Tienda Test',
            'tamano' => '15',
            'id_estado' => $estadoAlquilada->id,
            'infraestructura_piso_id' => $piso->id,
            'cliente_id' => $this->client->id,
        ]);

        // Subscription (expired) - withoutEvents to avoid automatic observer releasing store early during test setup
        $this->subscription = Suscripciones::withoutEvents(fn () => Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $piso->id,
            'fecha_inicio' => '2026-05-01',
            'fecha_fin' => '2026-05-31',
            'tipo' => '1 mes',
            'precio' => 1200.00,
        ]));

        // Generate cobros manually since events are disabled
        $this->subscription->generarCobrosMensuales();
    }

    public function test_expired_subscription_soft_deletes_client_but_keeps_payments_and_history(): void
    {
        $this->actingAs($this->adminUser);

        // Assert setup has 1 cobro
        $this->assertGreaterThan(0, $this->subscription->cobros()->count());
        $cobro = $this->subscription->cobros->first();

        // Create a verified payment
        $pago = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $cobro->id,
            'monto_pagado' => 1200.00,
            'pago_pendiente' => 0,
            'fecha_pago' => '2026-05-05',
            'metodo_pago' => 'efectivo',
            'estado_verificacion' => 'verificado',
        ]);

        // Process expired subscriptions as of today (June 8, 2026, which is > May 31)
        $released = app(ExpiredSubscriptionsService::class)->process('2026-06-08');
        $this->assertEquals(1, $released);

        // Client and user should be soft-deleted (since subscription is expired and has no future ones)
        $this->client->refresh();
        $this->assertTrue($this->client->trashed());
        $this->assertTrue($this->client->user->trashed());

        // Subscriptions, cobros and pagos must NOT be deleted
        $this->assertDatabaseHas('suscripciones', ['id' => $this->subscription->id]);
        $this->assertDatabaseHas('suscripciones_cobros', ['id' => $cobro->id]);
        $this->assertDatabaseHas('suscripciones_pagos', ['id' => $pago->id]);

        // Verify that suscription.cliente returns the soft-deleted client correctly because of withTrashed()
        $subFromDb = Suscripciones::with('cliente.user')->find($this->subscription->id);
        $this->assertNotNull($subFromDb->cliente);
        $this->assertEquals('Juan Perez', $subFromDb->cliente->nombre_completo);

        // Verify BalanceSuscripciones (Auditoría Financiera) page displays the name
        $page = new BalanceSuscripciones();
        $page->bootedInteractsWithTable();
        $tableConfig = $page->getTable();
        $inquilinoCol = $tableConfig->getColumns()['cliente.user.nombres'] ?? null;
        $this->assertNotNull($inquilinoCol);
        
        $inquilinoCol->record($this->subscription);
        $state = $inquilinoCol->getState();
        $this->assertEquals('Juan', $state);

        $u = $this->subscription->cliente?->user;
        $this->assertNotNull($u);
        $this->assertEquals('Juan Perez', trim($u->nombres . ' ' . $u->apellido_paterno));

        // Verify SuscripcionesTable (Gestión de Contratos) displays client name instead of N/A
        $component = \Mockery::mock(\Livewire\Component::class, \Filament\Tables\Contracts\HasTable::class);
        $component->shouldReceive('getTableRecordKey')->andReturnUsing(fn ($record) => (string)$record->id);
        $table = new \Filament\Tables\Table($component);
        $tableConfigContratos = SuscripcionesTable::configure($table);
        $clienteCol = $tableConfigContratos->getColumns()['cliente'] ?? null;
        $this->assertNotNull($clienteCol);
        $clienteText = $clienteCol->record($this->subscription)->getState();
        $this->assertEquals($this->client->id . ' - Juan Perez', $clienteText);
    }

    public function test_morosos_views_include_soft_deleted_clients(): void
    {
        $this->actingAs($this->adminUser);

        // Ensure the cobro is vencido (underfunded)
        $cobro = $this->subscription->cobros->first();
        $cobro->update([
            'estado' => 'vencido',
            'fecha_vencimiento' => '2026-05-15',
        ]);

        // Soft delete client
        $this->client->delete();
        $this->assertTrue($this->client->refresh()->trashed());

        // Verify ReporteMorosidad (Clientes en Mora) page displays name
        $page = new ReporteMorosidad();
        $page->bootedInteractsWithTable();
        $tableConfig = $page->getTable();
        
        $nombresCol = $tableConfig->getColumns()['suscripcion.cliente.user.nombres'] ?? null;
        $this->assertNotNull($nombresCol);
        $this->assertEquals('Juan', $nombresCol->record($cobro)->getState());

        $apellidosCol = $tableConfig->getColumns()['suscripcion.cliente.user.apellido_paterno'] ?? null;
        $this->assertNotNull($apellidosCol);
        $this->assertEquals('Perez', $apellidosCol->record($cobro)->getState());

        $u = $cobro->suscripcion?->cliente?->user;
        $this->assertNotNull($u);
        $this->assertEquals('Juan Perez', trim($u->nombres . ' ' . $u->apellido_paterno));

        // Verify TopMorososWidget displays soft-deleted client names
        $widget = new TopMorososWidget();
        $widget->bootedInteractsWithTable();
        $widgetTable = $widget->getTable();
        
        $queryResult = $widgetTable->getQuery()->get();
        $this->assertGreaterThan(0, $queryResult->count());
        $this->assertTrue($queryResult->contains('id', $this->client->id));

        $widgetInquilinoCol = $widgetTable->getColumns()['nombre_completo'] ?? null;
        $this->assertNotNull($widgetInquilinoCol);
        
        $widgetRecord = $queryResult->where('id', $this->client->id)->first();
        $this->assertEquals('Juan Perez', $widgetInquilinoCol->record($widgetRecord)->getState());
    }
}
