<?php

namespace Tests\Feature;

use App\Filament\Resources\SuscripcionesPagos\Pages\ListSuscripcionesPagos;
use App\Models\Clientes;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use App\Models\SuscripcionesTarifas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminManuallyCreatedPaymentsTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $client;
    protected $shop;
    protected $charge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Create client
        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '1212121',
            'numero_celular' => '71212121',
            'genero' => 'femenino',
        ]);

        // Create infrastructure, floor, and shop
        $infra = Infraestructuras::create([
            'nombre' => 'Mall Test Center',
            'ubicacion' => 'La Paz Centro',
            'pisos' => 1,
        ]);

        $piso = InfraestructurasPisos::create([
            'nombre' => 'Piso 1',
            'infraestructura_id' => $infra->id,
        ]);

        $this->shop = InfraestructurasTiendas::create([
            'numero' => 101,
            'tamano' => '10',
            'id_estado' => 1,
            'infraestructura_piso_id' => $piso->id,
        ]);

        $fee = SuscripcionesTarifas::create([
            'tamano_min' => 5,
            'tamano_max' => 15,
            'etiqueta' => 'Tarifa Standard',
            'tipo' => 'mensual',
            'precio' => 1000.00,
        ]);

        $subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'suscripciones_tarifa_id' => $fee->id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'estado' => 'activo',
            'tipo' => '1 mes',
            'precio' => 1000.00,
        ]);

        $this->charge = SuscripcionesCobros::create([
            'suscripcion_id' => $subscription->id,
            'concepto' => 'Cobro Test',
            'monto' => 1000.00,
            'fecha_inicio' => '2026-06-01',
            'fecha_vencimiento' => '2026-06-30',
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Test payments are correctly filtered in each List tab.
     */
    public function test_payment_tabs_filter_by_creator_and_status(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Manually created admin payment (defaults to creado_por_admin = true)
        $adminPayment = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 500,
            'pago_pendiente' => 500,
            'fecha_pago' => '2026-06-05',
            'metodo_pago' => 'efectivo',
            'nombre_pagador' => 'Sofia Admin',
        ]);

        // 2. Client-reported pending payment
        $clientPendingPayment = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 100,
            'pago_pendiente' => 400,
            'fecha_pago' => '2026-06-05',
            'metodo_pago' => 'qr',
            'estado_verificacion' => 'pendiente',
            'creado_por_admin' => false,
        ]);

        // 3. Client-reported verified/approved payment
        $clientVerifiedPayment = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 100,
            'pago_pendiente' => 300,
            'fecha_pago' => '2026-06-05',
            'metodo_pago' => 'qr',
            'estado_verificacion' => 'verificado',
            'creado_por_admin' => false,
        ]);

        // 4. Client-reported rejected payment
        $clientRejectedPayment = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 100,
            'pago_pendiente' => 200,
            'fecha_pago' => '2026-06-05',
            'metodo_pago' => 'qr',
            'estado_verificacion' => 'rechazado',
            'creado_por_admin' => false,
        ]);

        // Test using Livewire component to simulate active tabs
        // Check "Pagos creados" (creados tab)
        Livewire::test(ListSuscripcionesPagos::class)
            ->set('activeTab', 'creados')
            ->assertCanSeeTableRecords([$adminPayment])
            ->assertCanNotSeeTableRecords([$clientPendingPayment, $clientVerifiedPayment, $clientRejectedPayment]);

        // Check "Solicitudes de Pago" (pendientes tab)
        Livewire::test(ListSuscripcionesPagos::class)
            ->set('activeTab', 'pendientes')
            ->assertCanSeeTableRecords([$clientPendingPayment])
            ->assertCanNotSeeTableRecords([$adminPayment, $clientVerifiedPayment, $clientRejectedPayment]);

        // Check "Pagos Confirmados" (verificados tab)
        Livewire::test(ListSuscripcionesPagos::class)
            ->set('activeTab', 'verificados')
            ->assertCanSeeTableRecords([$clientVerifiedPayment])
            ->assertCanNotSeeTableRecords([$adminPayment, $clientPendingPayment, $clientRejectedPayment]);

        // Check "Solicitudes Rechazadas" (rechazados tab)
        Livewire::test(ListSuscripcionesPagos::class)
            ->set('activeTab', 'rechazados')
            ->assertCanSeeTableRecords([$clientRejectedPayment])
            ->assertCanNotSeeTableRecords([$adminPayment, $clientPendingPayment, $clientVerifiedPayment]);
    }
}
