<?php

namespace Tests\Feature;

use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use App\Models\SuscripcionesTarifas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PdfReportRoutesTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $client;
    protected $shop;
    protected $brand;
    protected $fee;
    protected $subscription;
    protected $charge;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create client user
        $this->user = User::factory()->create();
        $this->user->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $this->user->id,
            'ci' => '7777777',
            'numero_celular' => '77777777',
            'genero' => 'masculino',
        ]);

        // Piso
        $piso = \App\Models\InfraestructurasPisos::first();
        if (!$piso) {
            $infra = \App\Models\Infraestructuras::first() ?? \App\Models\Infraestructuras::create([
                'nombre' => 'Mall Gran Vía',
                'ubicacion' => 'La Paz',
            ]);
            $piso = \App\Models\InfraestructurasPisos::create([
                'nombre' => 'Piso 1',
                'infraestructura_id' => $infra->id,
            ]);
        }

        // Shop
        $this->shop = InfraestructurasTiendas::create([
            'numero' => 909,
            'tamano' => '15x15',
            'id_estado' => 1,
            'cliente_id' => $this->client->id,
            'infraestructura_piso_id' => $piso->id,
        ]);

        // Brand
        $this->brand = Marcas::create([
            'nombre' => 'Nike Test PDF',
            'estado' => 'activo',
            'cliente_id' => $this->client->id,
        ]);

        // Fee (tarifa)
        $this->fee = SuscripcionesTarifas::create([
            'tamano_min' => 10,
            'tamano_max' => 20,
            'etiqueta' => 'Tarifa Normal',
            'tipo' => 'mensual',
            'precio' => 1000.00,
        ]);

        // Subscription
        $this->subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'marca_id' => $this->brand->id,
            'suscripciones_tarifa_id' => $this->fee->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addYear()->toDateString(),
            'estado' => 'activo',
            'tipo' => 'mensual',
            'precio' => 1000.00,
        ]);

        // Charge (cobro) - automatically created, but we retrieve/create to be sure
        $this->charge = $this->subscription->cobros()->first() ?? SuscripcionesCobros::create([
            'suscripcion_id' => $this->subscription->id,
            'concepto' => 'Cobro Mensual Test',
            'monto' => 1000,
            'fecha_cobro' => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonth()->toDateString(),
            'fecha_inicio' => now()->toDateString(),
            'estado' => 'pendiente',
        ]);

        // Payment (pago)
        $this->payment = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'pago_pendiente' => 0,
            'fecha_pago' => now()->toDateString(),
            'metodo_pago' => 'efectivo',
            'estado_verificacion' => 'verificado',
            'nombre_pagador' => 'John Doe',
        ]);
    }

    /**
     * Test contract PDF download (authenticated).
     */
    public function test_contract_pdf_download(): void
    {
        $response = $this->actingAs($this->user)->get("/pdf/contrato/{$this->subscription->id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test charge PDF download (public).
     */
    public function test_charge_pdf_download(): void
    {
        $response = $this->get("/cobros/pdf/{$this->charge->id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test payment PDF download (public).
     */
    public function test_payment_pdf_download(): void
    {
        $response = $this->get("/pdf/pago/{$this->payment->id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test movements PDF download (public).
     */
    public function test_movements_pdf_download(): void
    {
        $response = $this->get("/pdf/suscripcion/movimiento/{$this->subscription->id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
