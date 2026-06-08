<?php

namespace Tests\Feature;

use App\Filament\Resources\SuscripcionesPagos\Pages\CreateSuscripcionesPagos;
use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPartialPaymentsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payment_form_selects_oldest_unpaid_charge_for_shop(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');

        $cliente = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '7755331',
            'numero_celular' => '70000009',
            'genero' => 'masculino',
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Cobros Antiguos',
            'pisos' => 1,
            'ubicacion' => 'La Paz',
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infraestructura->id,
            'nombre' => 'Piso 1',
        ]);

        $estado = EstadoTienda::firstOrCreate(['estado' => 'Alquilada']);

        $tienda = InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'cliente_id' => $cliente->id,
            'numero' => 'F-101',
            'nombre' => 'Flores Test',
            'tamano' => '20',
            'id_estado' => $estado->id,
        ]);

        $suscripcion = Suscripciones::create([
            'cliente_id' => $cliente->id,
            'infraestructuras_tienda_id' => $tienda->id,
            'infraestructuras_piso_id' => $piso->id,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2027-02-28',
            'tipo' => '1 año',
            'precio' => 12000.00,
        ]);

        $cobroVencidoAntiguo = $suscripcion->cobros()
            ->whereDate('fecha_vencimiento', '2026-05-01')
            ->firstOrFail();

        $suscripcion->cobros()
            ->whereDate('fecha_vencimiento', '<', '2026-05-01')
            ->update(['estado' => 'pagado', 'saldo_pendiente' => 0]);

        $cobroVencidoAntiguo->update(['estado' => 'vencido']);

        $suscripcion->cobros()
            ->whereDate('fecha_vencimiento', '2026-06-01')
            ->update(['estado' => 'vencido']);

        $suscripcion->cobros()
            ->whereDate('fecha_vencimiento', '>=', '2026-07-01')
            ->update(['estado' => 'pendiente']);

        $method = new \ReflectionMethod(
            \App\Filament\Resources\SuscripcionesPagos\Schemas\SuscripcionesPagosForm::class,
            'buscarCobroMasAntiguoPorTienda'
        );

        $selectedCobro = $method->invoke(null, $tienda->id);

        $this->assertSame($cobroVencidoAntiguo->id, $selectedCobro?->id);
        $this->assertSame('vencido', $selectedCobro?->estado);
        $this->assertSame('2026-05-01', $selectedCobro?->fecha_vencimiento);
    }

    public function test_admin_partial_payment_creates_partial_charge_on_selected_date(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');

        $cliente = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '9911223',
            'numero_celular' => '70000001',
            'genero' => 'masculino',
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Pagos Parciales',
            'pisos' => 1,
            'ubicacion' => 'La Paz',
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infraestructura->id,
            'nombre' => 'Piso 1',
        ]);

        $estado = EstadoTienda::firstOrCreate(['estado' => 'Alquilada']);

        $tienda = InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'cliente_id' => $cliente->id,
            'numero' => 'P-101',
            'nombre' => 'Local Parcial',
            'tamano' => '20',
            'id_estado' => $estado->id,
        ]);

        $fechaInicio = Carbon::parse(now()->toDateString());
        $fechaCobroParcial = $fechaInicio->copy()->addDays(10)->toDateString();

        $suscripcion = Suscripciones::create([
            'cliente_id' => $cliente->id,
            'infraestructuras_tienda_id' => $tienda->id,
            'infraestructuras_piso_id' => $piso->id,
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_fin' => $fechaInicio->copy()->addMonthsNoOverflow(2)->subDay()->toDateString(),
            'tipo' => '2 meses',
            'precio' => 1000.00,
        ]);

        $cobro = $suscripcion->cobros()
            ->orderBy('fecha_vencimiento')
            ->firstOrFail();

        $this->assertEquals(1000.00, (float) $cobro->monto);

        Livewire::actingAs($admin)
            ->test(CreateSuscripcionesPagos::class)
            ->fillForm([
                'cliente_id' => $cliente->id,
                'tienda_id' => $tienda->id,
                'suscripcion_cobro_id' => $cobro->id,
                'localizacion' => 'Mall Pagos Parciales - Piso Piso 1',
                'monto_total' => 1000.00,
                'total_pagado' => 0,
                'saldo_restante' => 1000.00,
                'monto_pagado' => 650.00,
                'pago_pendiente' => 350.00,
                'fecha_pago' => $fechaInicio->toDateString(),
                'fecha_vencimiento' => $cobro->fecha_vencimiento,
                'metodo_pago' => 'efectivo',
                'nombre_pagador' => 'Administrador Test',
                'hora_pago' => now()->format('H:i:s'),
                'observaciones' => 'Pago parcial de prueba',
                'cobro_pendiente_opcion' => 'fecha_intermedia',
                'fecha_cobro_pendiente' => $fechaCobroParcial,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $pago = $cobro->pagos()->firstOrFail();

        $this->assertEquals(650.00, (float) $pago->monto_pagado);
        $this->assertEquals(350.00, (float) $pago->pago_pendiente);
        $this->assertEquals('parcial', $pago->estado_snapshot);

        $cobro->refresh();
        $this->assertEquals(650.00, (float) $cobro->monto);
        $this->assertEquals('pagado', $cobro->estado);

        $cobroParcial = SuscripcionesCobros::where('suscripcion_id', $suscripcion->id)
            ->where('es_parcial', true)
            ->firstOrFail();

        $this->assertEquals(350.00, (float) $cobroParcial->monto);
        $this->assertEquals('pendiente', $cobroParcial->estado);
        $this->assertEquals($fechaCobroParcial, $cobroParcial->fecha_vencimiento);
        $this->assertStringContainsString('Saldo pendiente de:', $cobroParcial->concepto);
    }

    public function test_admin_full_payment_does_not_create_partial_charge(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');

        $cliente = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '8899001',
            'numero_celular' => '70000002',
            'genero' => 'femenino',
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Pago Completo',
            'pisos' => 1,
            'ubicacion' => 'La Paz',
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infraestructura->id,
            'nombre' => 'Piso 1',
        ]);

        $estado = EstadoTienda::firstOrCreate(['estado' => 'Alquilada']);

        $tienda = InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'cliente_id' => $cliente->id,
            'numero' => 'C-101',
            'nombre' => 'Local Completo',
            'tamano' => '20',
            'id_estado' => $estado->id,
        ]);

        $fechaInicio = Carbon::parse(now()->toDateString());

        $suscripcion = Suscripciones::create([
            'cliente_id' => $cliente->id,
            'infraestructuras_tienda_id' => $tienda->id,
            'infraestructuras_piso_id' => $piso->id,
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_fin' => $fechaInicio->copy()->addMonthNoOverflow()->subDay()->toDateString(),
            'tipo' => '1 mes',
            'precio' => 500.00,
        ]);

        $cobro = $suscripcion->cobros()->firstOrFail();

        Livewire::actingAs($admin)
            ->test(CreateSuscripcionesPagos::class)
            ->fillForm([
                'cliente_id' => $cliente->id,
                'tienda_id' => $tienda->id,
                'suscripcion_cobro_id' => $cobro->id,
                'monto_total' => 500.00,
                'total_pagado' => 0,
                'saldo_restante' => 500.00,
                'monto_pagado' => 500.00,
                'pago_pendiente' => 0,
                'fecha_pago' => $fechaInicio->toDateString(),
                'fecha_vencimiento' => $cobro->fecha_vencimiento,
                'metodo_pago' => 'efectivo',
                'nombre_pagador' => 'Administrador Test',
                'hora_pago' => now()->format('H:i:s'),
                'observaciones' => 'Pago completo de prueba',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $pago = $cobro->pagos()->firstOrFail();

        $this->assertEquals(0.00, (float) $pago->pago_pendiente);
        $this->assertEquals('pagado', $pago->estado_snapshot);
        $this->assertFalse(
            SuscripcionesCobros::where('suscripcion_id', $suscripcion->id)
                ->where('es_parcial', true)
                ->exists()
        );
    }
}
