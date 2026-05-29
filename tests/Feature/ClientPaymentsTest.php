<?php

namespace Tests\Feature;

use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesTarifas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientPaymentsTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $client;
    protected $shop;
    protected $subscription;
    protected $fee;
    protected $charge;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create client user
        $this->user = User::factory()->create();
        $this->user->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $this->user->id,
            'ci' => '6666666',
            'numero_celular' => '76666666',
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
            'numero' => 905,
            'tamano' => '15x15',
            'id_estado' => 1,
            'cliente_id' => $this->client->id,
            'infraestructura_piso_id' => $piso->id,
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
            'suscripciones_tarifa_id' => $this->fee->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addYear()->toDateString(),
            'estado' => 'activo',
            'tipo' => 'mensual',
            'precio' => 1000.00,
        ]);

        // Charge (cobro) - automatically created by Suscripciones booted created event
        $this->charge = $this->subscription->cobros()->first();
    }

    public function test_report_payment_success_via_cash(): void
    {
        $comprobante = UploadedFile::fake()->image('recibo.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'metodo_pago' => 'efectivo',
            'nombre_pagador' => 'John Doe',
            'comprobante' => $comprobante,
        ]);

        $response->assertRedirect(route('cliente.estado-cuenta'));
        
        $this->assertDatabaseHas('suscripciones_pagos', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'metodo_pago' => 'efectivo',
            'nombre_pagador' => 'John Doe',
        ]);
    }

    public function test_report_payment_success_via_transfer(): void
    {
        $comprobante = UploadedFile::fake()->create('comprobante.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 500,
            'metodo_pago' => 'transferencia',
            'numero_transaccion' => 'TX-9999',
            'banco_origen' => 'Banco de Crédito',
            'comprobante' => $comprobante,
        ]);

        $response->assertRedirect(route('cliente.estado-cuenta'));

        $this->assertDatabaseHas('suscripciones_pagos', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 500,
            'metodo_pago' => 'transferencia',
            'numero_transaccion' => 'TX-9999',
            'banco_origen' => 'Banco de Crédito',
        ]);
    }

    public function test_report_payment_validation_fails_for_missing_transfer_fields(): void
    {
        $comprobante = UploadedFile::fake()->image('recibo.jpg');

        // When metodo_pago is 'transferencia', 'numero_transaccion' and 'banco_origen' are required
        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'metodo_pago' => 'transferencia',
            'comprobante' => $comprobante,
        ]);

        $response->assertSessionHasErrors(['numero_transaccion', 'banco_origen']);
    }

    public function test_report_payment_validation_fails_for_missing_cash_fields(): void
    {
        $comprobante = UploadedFile::fake()->image('recibo.jpg');

        // When metodo_pago is 'efectivo', 'nombre_pagador' is required
        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'metodo_pago' => 'efectivo',
            'comprobante' => $comprobante,
        ]);

        $response->assertSessionHasErrors(['nombre_pagador']);
    }
}
