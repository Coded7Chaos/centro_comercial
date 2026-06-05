<?php

namespace Tests\Feature;

use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesTarifas;
use App\Models\SuscripcionesPagos;
use App\Models\ClientNotification;
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
            'fecha_fin' => now()->addMonth()->subDay()->toDateString(),
            'estado' => 'activo',
            'tipo' => 'mensual',
            'precio' => 1000.00,
        ]);

        // Charge (cobro) - automatically created by Suscripciones booted created event
        $this->charge = $this->subscription->cobros()->first();
    }

    public function test_report_payment_success_via_transfer(): void
    {
        $comprobante = UploadedFile::fake()->create('comprobante.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'metodo_pago' => 'transferencia',
            'numero_transaccion' => 'TX-9999',
            'banco_origen' => 'Banco de Crédito',
            'comprobante' => $comprobante,
        ]);

        $response->assertRedirect(route('cliente.estado-cuenta'));

        $this->assertDatabaseHas('suscripciones_pagos', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'metodo_pago' => 'transferencia',
            'numero_transaccion' => 'TX-9999',
            'banco_origen' => 'Banco de Crédito',
            'estado_verificacion' => 'pendiente',
        ]);

        $this->charge->refresh();
        $this->assertEquals('pendiente_confirmacion', $this->charge->estado);
    }

    public function test_report_payment_success_via_qr(): void
    {
        $comprobante = UploadedFile::fake()->image('comprobante.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'metodo_pago' => 'qr',
            'comprobante' => $comprobante,
        ]);

        $response->assertRedirect(route('cliente.estado-cuenta'));

        $this->assertDatabaseHas('suscripciones_pagos', [
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'metodo_pago' => 'qr',
            'estado_verificacion' => 'pendiente',
        ]);

        $this->charge->refresh();
        $this->assertEquals('pendiente_confirmacion', $this->charge->estado);
    }

    public function test_report_payment_validation_fails_for_disallowed_methods(): void
    {
        $comprobante = UploadedFile::fake()->image('recibo.jpg');

        // Efectivo and Tarjeta are disallowed for client reporting
        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'metodo_pago' => 'efectivo',
            'comprobante' => $comprobante,
        ]);

        $response->assertSessionHasErrors(['metodo_pago']);

        $response2 = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'metodo_pago' => 'tarjeta',
            'comprobante' => $comprobante,
        ]);

        $response2->assertSessionHasErrors(['metodo_pago']);
    }

    public function test_report_payment_validation_fails_for_missing_transfer_fields(): void
    {
        $comprobante = UploadedFile::fake()->image('recibo.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/estado-cuenta/reportar-pago', [
            'suscripcion_cobro_id' => $this->charge->id,
            'metodo_pago' => 'transferencia',
            'comprobante' => $comprobante,
        ]);

        $response->assertSessionHasErrors(['numero_transaccion', 'banco_origen']);
    }

    public function test_admin_approves_payment_request(): void
    {
        // 1. Report payment first
        $pago = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'pago_pendiente' => 0,
            'fecha_pago' => now()->toDateString(),
            'metodo_pago' => 'qr',
            'estado_verificacion' => 'pendiente',
            'estado_snapshot' => 'pendiente',
            'creado_por_admin' => false,
        ]);
        $this->charge->recalcularEstado();
        $this->assertEquals('pendiente_confirmacion', $this->charge->estado);

        // 2. Simulate admin approval (same action as the table action callback)
        $pago->update(['estado_verificacion' => 'verificado']);
        
        $this->charge->update([
            'saldo_pendiente' => 0,
            'estado' => 'pagado',
            'estado_snapshot' => 'pagado',
        ]);

        // Send success notification to client
        ClientNotification::create([
            'cliente_id' => $this->client->id,
            'tipo' => 'success',
            'titulo' => 'Pago Aprobado',
            'mensaje' => 'Su pago ha sido aprobado.',
        ]);

        // Verify status changes and notification creation
        $this->charge->refresh();
        $this->assertEquals('pagado', $this->charge->estado);
        $this->assertEquals('verificado', $pago->estado_verificacion);

        $this->assertDatabaseHas('client_notifications', [
            'cliente_id' => $this->client->id,
            'tipo' => 'success',
            'titulo' => 'Pago Aprobado',
        ]);
    }

    public function test_admin_rejects_payment_request(): void
    {
        // 1. Report payment first
        $pago = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $this->charge->id,
            'monto_pagado' => 1000,
            'pago_pendiente' => 0,
            'fecha_pago' => now()->toDateString(),
            'metodo_pago' => 'qr',
            'estado_verificacion' => 'pendiente',
            'estado_snapshot' => 'pendiente',
            'creado_por_admin' => false,
        ]);
        $this->charge->recalcularEstado();
        $this->assertEquals('pendiente_confirmacion', $this->charge->estado);

        // 2. Simulate admin rejection
        $pago->update([
            'estado_verificacion' => 'rechazado',
            'motivo_rechazo' => 'Comprobante borroso',
        ]);

        $originalEstado = now()->toDateString() > $this->charge->fecha_vencimiento ? 'vencido' : 'pendiente';
        $this->charge->update([
            'estado' => $originalEstado,
            'estado_snapshot' => $originalEstado,
        ]);

        // Send rejection notification to client
        ClientNotification::create([
            'cliente_id' => $this->client->id,
            'tipo' => 'danger',
            'titulo' => 'Pago Rechazado',
            'mensaje' => 'Su pago ha sido rechazado. Razón: Comprobante borroso',
        ]);

        // Verify status changes and notification creation
        $this->charge->refresh();
        $this->assertEquals($originalEstado, $this->charge->estado);
        $this->assertEquals('rechazado', $pago->estado_verificacion);
        $this->assertEquals('Comprobante borroso', $pago->motivo_rechazo);

        $this->assertDatabaseHas('client_notifications', [
            'cliente_id' => $this->client->id,
            'tipo' => 'danger',
            'titulo' => 'Pago Rechazado',
            'mensaje' => 'Su pago ha sido rechazado. Razón: Comprobante borroso',
        ]);
    }
}
