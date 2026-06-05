<?php

namespace Tests\Feature;

use App\Filament\Resources\SuscripcionesCobros\SuscripcionesCobrosResource;
use App\Models\Clientes;
use App\Models\DescuentoTiempo;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use App\Models\SuscripcionesTarifas;
use App\Models\TamanoEtiqueta;
use App\Models\TamanoPrecio;
use App\Models\User;
use App\Support\ActiveInfraestructura;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuscripcionesCustomWizardTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected $clientUser;

    protected $client;

    protected $shop;

    protected $fee;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Run roles seeder
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Create client user and client relation
        $this->clientUser = User::factory()->create();
        $this->clientUser->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $this->clientUser->id,
            'ci' => '7777777',
            'numero_celular' => '77777777',
            'genero' => 'femenino',
        ]);

        // Get a floor and create a shop
        $piso = InfraestructurasPisos::first();
        if (! $piso) {
            $infra = Infraestructuras::create([
                'nombre' => 'Mall Test Vía',
                'ubicacion' => 'La Paz',
            ]);
            $piso = InfraestructurasPisos::create([
                'nombre' => 'Piso 1',
                'infraestructura_id' => $infra->id,
            ]);
        }

        $this->shop = InfraestructurasTiendas::create([
            'numero' => 701,
            'tamano' => '10x10',
            'id_estado' => 1,
            'infraestructura_piso_id' => $piso->id,
        ]);

        // Clear new pricing and discount tables
        TamanoPrecio::query()->delete();
        TamanoEtiqueta::query()->delete();
        DescuentoTiempo::query()->delete();

        // Create the new size label and price configuration
        $etiqueta = TamanoEtiqueta::create([
            'nombre' => 'Tarifa Test',
            'desde' => 5.0,
            'hasta' => 15.0,
        ]);

        TamanoPrecio::create([
            'tamano_etiqueta_id' => $etiqueta->id,
            'precio_mensual' => 500.00,
        ]);

        // Seed 10% discount for min 3 months
        DescuentoTiempo::create([
            'min_meses' => 3,
            'descuento' => 10.00,
        ]);

        // Create a standard reference tariff for compatibility
        $this->fee = SuscripcionesTarifas::create([
            'tamano_min' => 5,
            'tamano_max' => 15,
            'etiqueta' => 'Tarifa Test',
            'tipo' => 'mensual',
            'precio' => 500.00,
        ]);
    }

    public function test_client_cannot_access_custom_wizard_endpoints(): void
    {
        $this->actingAs($this->clientUser);

        // 1. Crear view
        $this->get('/admin/suscripciones-custom/crear')->assertStatus(403);

        // 2. Tienda precio
        $this->get("/admin/suscripciones-custom/tienda-precio/{$this->shop->id}")->assertStatus(403);

        // 3. Guardar subscription
        $this->post('/admin/suscripciones-custom/guardar', [])->assertStatus(403);

        // 4. Contrato descargar
        $this->get('/admin/suscripciones-custom/contrato/1/descargar')->assertStatus(403);

        // 5. Pago view
        $this->get('/admin/suscripciones-custom/pago/1')->assertStatus(403);

        // 6. Registrar pago
        $this->post('/admin/suscripciones-custom/pago/1/guardar', [])->assertStatus(403);
    }

    public function test_admin_can_access_crear_custom_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/suscripciones-custom/crear');

        $response->assertStatus(200);
        $response->assertSee('Crear Contrato de Arrendamiento');
        $response->assertSee($this->client->nombre_completo);
        $response->assertSee('Local N° '.$this->shop->numero);
    }

    public function test_ajax_tienda_precio_endpoint(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get("/admin/suscripciones-custom/tienda-precio/{$this->shop->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->shop->id,
            'numero' => $this->shop->numero,
            'precio_mensual_base' => 500.00,
            'descuento_porcentaje' => 0.00,
            'descuento_monto_mensual' => 0.00,
            'precio_mensual_con_descuento' => 500.00,
            'precio_total_sin_descuento' => 500.00,
            'precio_total_con_descuento' => 500.00,
            'pago_inicial' => 500.00,
            'etiqueta' => 'Tarifa Test',
        ]);
    }

    public function test_store_validation_fails_for_missing_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/suscripciones-custom/guardar', []);

        $response->assertSessionHasErrors(['cliente_id', 'infraestructuras_tienda_id', 'duracion_valor', 'duracion_unidad', 'fecha_inicio']);
    }

    public function test_store_prevents_duplicate_subscription_in_same_period(): void
    {
        // First create a subscription
        Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        // Try to create overlapping subscription
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/suscripciones-custom/guardar', [
                'cliente_id' => $this->client->id,
                'infraestructuras_tienda_id' => $this->shop->id,
                'duracion_valor' => 1,
                'duracion_unidad' => 'meses',
                'fecha_inicio' => '2026-06-15',
            ]);

        $response->assertSessionHasErrors(['infraestructuras_tienda_id']);
    }

    public function test_successful_creation_redirects_to_pago_custom(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/suscripciones-custom/guardar', [
                'cliente_id' => $this->client->id,
                'infraestructuras_tienda_id' => $this->shop->id,
                'duracion_valor' => 3,
                'duracion_unidad' => 'meses',
                'fecha_inicio' => '2026-06-01',
            ]);

        $suscripcion = Suscripciones::where('cliente_id', $this->client->id)
            ->where('infraestructuras_tienda_id', $this->shop->id)
            ->first();

        $this->assertNotNull($suscripcion);
        $this->assertNull($suscripcion->marca_id);
        $this->assertEquals('2026-06-01', $suscripcion->fecha_inicio);
        // 3 months means 2026-06-01 to 2026-08-31
        $this->assertEquals('2026-08-31', $suscripcion->fecha_fin);
        $this->assertEquals('3 meses', $suscripcion->tipo);
        $this->assertEquals(1350.00, $suscripcion->precio);

        $response->assertRedirect(route('admin.suscripciones.pago-custom', [
            'id' => $suscripcion->id,
            'download_pdf' => 1,
        ]));
    }

    public function test_subscription_creation_generates_one_charge_per_month_with_initial_payment_and_guarantee(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/admin/suscripciones-custom/guardar', [
                'cliente_id' => $this->client->id,
                'infraestructuras_tienda_id' => $this->shop->id,
                'duracion_valor' => 3,
                'duracion_unidad' => 'meses',
                'fecha_inicio' => '2026-06-15',
            ])
            ->assertRedirect();

        $suscripcion = Suscripciones::where('cliente_id', $this->client->id)
            ->where('infraestructuras_tienda_id', $this->shop->id)
            ->firstOrFail();

        $cobros = $suscripcion->cobros()
            ->orderBy('fecha_vencimiento')
            ->get();

        $this->assertCount(3, $cobros);
        $this->assertEquals(['2026-06-15', '2026-07-15', '2026-08-15'], $cobros->pluck('fecha_vencimiento')->all());
        $this->assertEquals([900.00, 450.00, 450.00], $cobros->pluck('monto')->map(fn ($monto) => (float) $monto)->all());
        $this->assertStringContainsString('Garantía', $cobros->first()->concepto);
        $this->assertTrue($cobros->every(fn (SuscripcionesCobros $cobro) => $cobro->observaciones === 'Cobro generado automáticamente'));
    }

    public function test_admin_cobros_page_separates_paid_pending_and_overdue_charges(): void
    {
        $startDate = Carbon::parse(now()->toDateString());

        $subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => $startDate->toDateString(),
            'fecha_fin' => $startDate->copy()->addMonthsNoOverflow(3)->subDay()->toDateString(),
            'tipo' => '3 meses',
            'precio' => 1350.00,
        ]);

        $firstCharge = $subscription->cobros()
            ->orderBy('fecha_vencimiento')
            ->firstOrFail();

        SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $firstCharge->id,
            'monto_pagado' => $firstCharge->monto,
            'pago_pendiente' => 0,
            'fecha_pago' => $startDate->toDateString(),
            'fecha_hora_operacion' => now(),
            'metodo_pago' => 'efectivo',
            'nombre_pagador' => 'Pago Test',
            'estado_verificacion' => 'verificado',
            'monto_total' => $firstCharge->monto,
            'estado_snapshot' => 'pagado',
        ]);

        $pastPendingCharge = SuscripcionesCobros::create([
            'suscripcion_id' => $subscription->id,
            'concepto' => 'Cobro Mensual vencido - Local Test',
            'monto' => 450.00,
            'fecha_inicio' => $startDate->copy()->subDay()->toDateString(),
            'fecha_vencimiento' => $startDate->copy()->subDay()->toDateString(),
            'estado' => 'pendiente',
            'observaciones' => 'Cobro vencido de prueba',
            'es_parcial' => false,
        ]);

        $firstCharge->update([
            'estado' => 'pagado',
            'saldo_pendiente' => 0,
            'estado_snapshot' => 'pagado',
        ]);

        $infraestructuraId = $this->shop->piso->infraestructura_id;

        $this->withSession([ActiveInfraestructura::SESSION_KEY => $infraestructuraId]);

        $this->actingAs($this->adminUser)
            ->get(SuscripcionesCobrosResource::getUrl('index'))
            ->assertOk();

        $futurePendingIds = $subscription->cobros()
            ->orderBy('fecha_vencimiento')
            ->where('id', '!=', $firstCharge->id)
            ->where('id', '!=', $pastPendingCharge->id)
            ->pluck('id')
            ->all();

        $visibleMonthlyIds = SuscripcionesCobrosResource::getEloquentQuery()
            ->where('es_parcial', false)
            ->whereNotIn('estado', ['pagado', 'anulado'])
            ->where('fecha_vencimiento', '>=', now()->toDateString())
            ->pluck('id')
            ->all();

        $morosoIds = SuscripcionesCobrosResource::getEloquentQuery()
            ->where('es_parcial', false)
            ->whereNotIn('estado', ['pagado', 'anulado'])
            ->where('fecha_vencimiento', '<', now()->toDateString())
            ->pluck('id')
            ->all();

        $allActiveCobroIds = SuscripcionesCobrosResource::getEloquentQuery()
            ->whereNotIn('estado', ['pagado', 'anulado'])
            ->pluck('id')
            ->all();

        $this->assertEqualsCanonicalizing($futurePendingIds, array_intersect($futurePendingIds, $visibleMonthlyIds));
        $this->assertNotContains($firstCharge->id, $visibleMonthlyIds);
        $this->assertNotContains($pastPendingCharge->id, $visibleMonthlyIds);
        $this->assertContains($pastPendingCharge->id, $morosoIds);
        $this->assertNotContains($firstCharge->id, $allActiveCobroIds);
    }

    public function test_pago_view_calculates_correct_initial_payment(): void
    {
        // 1. One month subscription
        $sub1 = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.suscripciones.pago-custom', $sub1->id));

        $response1->assertStatus(200);
        $response1->assertViewHas('pago_inicial', 500.00);

        // 2. Multi-month subscription (> 1 month rent) -> Requires 2 months (1 month rent + 1 month security deposit)
        $sub3 = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-07-01',
            'fecha_fin' => '2026-09-30',
            'tipo' => '3 meses',
            'precio' => 1350.00,
        ]);

        $response3 = $this->actingAs($this->adminUser)
            ->get(route('admin.suscripciones.pago-custom', $sub3->id));

        $response3->assertStatus(200);
        $response3->assertViewHas('pago_inicial', 900.00); // 2 months rent equivalent (450 * 2)
    }

    public function test_store_payment_verifies_and_splits_if_partial(): void
    {
        $subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-08-31',
            'tipo' => '3 meses',
            'precio' => 1350.00,
        ]);

        $cobro = SuscripcionesCobros::where('suscripcion_id', $subscription->id)
            ->orderBy('id', 'asc')
            ->first();
        $this->assertNotNull($cobro);
        $this->assertEquals(900.00, $cobro->monto);

        // Submit partial payment of 700.00 for the 900.00 charge. Leftover 200.00 should be split.
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.suscripciones.pagar-custom', $subscription->id), [
                'suscripcion_cobro_id' => $cobro->id,
                'monto_pagado' => 700.00,
                'fecha_pago' => '2026-06-01',
                'metodo_pago' => 'transferencia',
                'nombre_titular' => 'Sofia Suarez',
                'referencia' => 987654,
                'banco_origen' => 'BCP',
                'comprobante' => \Illuminate\Http\UploadedFile::fake()->create('comprobante.pdf', 100),
            ]);

        $response->assertRedirect('/admin/suscripciones');

        // Original cobro is adjusted to 700.00 and marked as paid
        $cobro->refresh();
        $this->assertEquals(700.00, $cobro->monto);
        $this->assertEquals('pagado', $cobro->estado);

        // A new deferred cobro is created for the remaining balance of 200.00
        $deferredCobro = SuscripcionesCobros::where('suscripcion_id', $subscription->id)
            ->where('es_parcial', true)
            ->first();

        $this->assertNotNull($deferredCobro);
        $this->assertEquals(200.00, $deferredCobro->monto);
        $this->assertEquals('pendiente', $deferredCobro->estado);
        $this->assertStringContainsString('Saldo pendiente de:', $deferredCobro->concepto);

        // The payment record exists with verification snapshot
        $this->assertDatabaseHas('suscripciones_pagos', [
            'suscripcion_cobro_id' => $cobro->id,
            'monto_pagado' => 700.00,
            'estado_verificacion' => 'verificado',
            'estado_snapshot' => 'pagado',
        ]);
    }

    public function test_descargar_contrato_pdf_returns_binary_response(): void
    {
        $subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.suscripciones.contrato-descargar', $subscription->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_descargar_contrato_word_returns_docx_binary_response(): void
    {
        $subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.suscripciones.contrato-descargar', ['id' => $subscription->id, 'format' => 'word']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_client_cannot_access_custom_renewal_endpoints(): void
    {
        $subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        $this->actingAs($this->clientUser);

        $this->get(route('admin.suscripciones.renovar-custom', $subscription->id))->assertStatus(403);
        $this->post(route('admin.suscripciones.guardar-renovacion', $subscription->id), [])->assertStatus(403);
    }

    public function test_admin_can_access_custom_renovar_page(): void
    {
        $parentSub = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.suscripciones.renovar-custom', $parentSub->id));

        $response->assertStatus(200);
        $response->assertSee('Renovar Contrato de Alquiler');
        $response->assertSee($this->client->nombre_completo);
        $response->assertSee('Local N° '.$this->shop->numero);
    }

    public function test_renewal_creation_and_no_double_monto_inicial(): void
    {
        $parentSub = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'infraestructuras_piso_id' => $this->shop->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        // Post a renewal subscription for 3 months starting 2026-07-01
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.suscripciones.guardar-renovacion', $parentSub->id), [
                'duracion_valor' => 3,
                'duracion_unidad' => 'meses',
                'fecha_inicio' => '2026-07-01',
            ]);

        $renewalSub = Suscripciones::where('renovacion_de_id', $parentSub->id)->first();
        $this->assertNotNull($renewalSub);
        $this->assertEquals('2026-07-01', $renewalSub->fecha_inicio);
        $this->assertEquals('2026-09-30', $renewalSub->fecha_fin);
        $this->assertEquals('3 meses', $renewalSub->tipo);

        // It should redirect to payment page
        $response->assertRedirect(route('admin.suscripciones.pago-custom', [
            'id' => $renewalSub->id,
            'download_pdf' => 1,
        ]));

        // Now test the payment calculation for this renewal subscription (it should NOT double the first month's payment!)
        $pagoResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.suscripciones.pago-custom', $renewalSub->id));

        $pagoResponse->assertStatus(200);
        // Under calcularAlquiler, a 3-month lease for $500 monthly reference with 10% discount is:
        // regular price is $500, discounted price is $450/month.
        // For a normal contract, pago_inicial is $450 * 2 = $900 (rent + guarantee).
        // But for a renewal, it must be only $450 (rent only, no guarantee deposit!).
        $pagoResponse->assertViewHas('pago_inicial', 450.00);
    }
}
