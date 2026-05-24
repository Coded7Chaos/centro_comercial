<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuscripcionesDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $alquilada = EstadoTienda::where('estado', 'Alquilada')->first();
            if (! $alquilada) {
                $this->command?->warn('No existe el estado "Alquilada". Corre EstadosTiendasSeeder primero.');
                return;
            }

            $clientes = $this->crearClientesDemo();

            // Idempotencia: si el primer cliente demo ya tiene contratos, asumimos que el seeder ya corrió.
            $idsClientes = collect($clientes)->pluck('id');
            if (Suscripciones::whereIn('cliente_id', $idsClientes)->exists()) {
                $this->command?->info('SuscripcionesDemoSeeder: ya existen contratos demo, se omite.');
                return;
            }

            $tiendas = $this->tomarTiendasDisponibles(count($clientes));

            if ($tiendas->count() < count($clientes)) {
                $this->command?->warn('No hay suficientes tiendas para asignar a los clientes demo. Crea más tiendas primero.');
                return;
            }

            // Asignar cada tienda a un cliente, marcarla como Alquilada y crearle una marca
            $asignaciones = [];
            foreach ($clientes as $i => $cliente) {
                $tienda = $tiendas[$i];

                $tienda->cliente_id = $cliente->id;
                $tienda->id_estado  = $alquilada->id;
                $tienda->save();

                $marca = Marcas::firstOrCreate(
                    ['nombre' => 'Marca ' . $cliente->user->nombres],
                    [
                        'cliente_id'  => $cliente->id,
                        'descripcion' => 'Marca demo de ' . $cliente->user->nombres,
                        'estado'      => 'activo',
                    ]
                );

                if (! $tienda->marcas()->where('marca_id', $marca->id)->exists()) {
                    $tienda->marcas()->attach($marca->id);
                }

                $asignaciones[] = ['cliente' => $cliente, 'tienda' => $tienda, 'marca' => $marca];
            }

            // 6 escenarios distintos
            $this->contratoMensualTotalmentePagado($asignaciones[0]);
            $this->contratoMensualConMorosidad($asignaciones[1]);
            $this->contratoTrimestralVencido($asignaciones[2]);
            $this->contratoAnualNuevo($asignaciones[3]);
            $this->contratoMensualConPagoParcial($asignaciones[4]);
            $this->contratoSemestralPagado($asignaciones[5]);

            $this->command?->info('SuscripcionesDemoSeeder: contratos, cobros y pagos demo creados.');
        });
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    private function crearClientesDemo(): array
    {
        $datos = [
            ['nombres' => 'Lucía',   'apellido_paterno' => 'Vargas',   'apellido_materno' => 'Mendoza', 'email' => 'lucia.demo@mall.com',  'ci' => '7001001'],
            ['nombres' => 'Mateo',   'apellido_paterno' => 'Quispe',   'apellido_materno' => 'Rojas',   'email' => 'mateo.demo@mall.com',  'ci' => '7001002'],
            ['nombres' => 'Sofía',   'apellido_paterno' => 'Suárez',   'apellido_materno' => 'Aliaga',  'email' => 'sofia.demo@mall.com',  'ci' => '7001003'],
            ['nombres' => 'Diego',   'apellido_paterno' => 'Choque',   'apellido_materno' => 'Ortega',  'email' => 'diego.demo@mall.com',  'ci' => '7001004'],
            ['nombres' => 'Camila',  'apellido_paterno' => 'Mamani',   'apellido_materno' => 'Vega',    'email' => 'camila.demo@mall.com', 'ci' => '7001005'],
            ['nombres' => 'Joaquín', 'apellido_paterno' => 'Flores',   'apellido_materno' => 'Lima',    'email' => 'joaquin.demo@mall.com','ci' => '7001006'],
        ];

        $clientes = [];
        foreach ($datos as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'nombres'          => $d['nombres'],
                    'apellido_paterno' => $d['apellido_paterno'],
                    'apellido_materno' => $d['apellido_materno'],
                    'password'         => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if (! $user->hasRole('cliente')) {
                $user->assignRole('cliente');
            }

            $cliente = Clientes::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'ci'              => $d['ci'],
                    'numero_celular'  => '7' . rand(1000000, 9999999),
                    'genero'          => collect(['masculino', 'femenino'])->random(),
                    'codigo_pais'     => '+591',
                ]
            );

            $cliente->setRelation('user', $user);
            $clientes[] = $cliente;
        }

        return $clientes;
    }

    private function tomarTiendasDisponibles(int $cantidad)
    {
        return InfraestructurasTiendas::query()
            ->whereNull('cliente_id')
            ->orderBy('id')
            ->limit($cantidad)
            ->get();
    }

    /**
     * Crea una suscripción y limpia el cobro inicial que dispara el observer,
     * para poder generar el histórico desde cero.
     */
    private function crearSuscripcionLimpia(array $a, string $tipo, float $precio, Carbon $inicio, Carbon $fin): Suscripciones
    {
        $sus = Suscripciones::create([
            'cliente_id'                  => $a['cliente']->id,
            'marca_id'                    => $a['marca']->id,
            'infraestructuras_tienda_id'  => $a['tienda']->id,
            'tipo'                        => $tipo,
            'precio'                      => $precio,
            'fecha_inicio'                => $inicio->toDateString(),
            'fecha_fin'                   => $fin->toDateString(),
        ]);

        // El observer creó el primer cobro automáticamente; lo quitamos para
        // controlar el histórico desde el seeder.
        $sus->cobros()->delete();

        return $sus;
    }

    private function crearCobro(Suscripciones $sus, string $tipoTexto, float $monto, Carbon $inicio, Carbon $vence, string $estadoInicial = 'pendiente'): SuscripcionesCobros
    {
        return SuscripcionesCobros::create([
            'suscripcion_id'    => $sus->id,
            'concepto'          => "Cobro {$tipoTexto} - " . $sus->infraestructurasTienda?->nombre,
            'monto'             => $monto,
            'fecha_inicio'      => $inicio->toDateString(),
            'fecha_vencimiento' => $vence->toDateString(),
            'estado'            => $estadoInicial,
        ]);
    }

    private function pagar(SuscripcionesCobros $cobro, float $monto, ?Carbon $fecha = null, ?string $metodo = null): SuscripcionesPagos
    {
        $fecha  = $fecha  ?? $cobro->fecha_vencimiento ? Carbon::parse($cobro->fecha_vencimiento) : now();
        $metodo = $metodo ?? collect(['efectivo', 'transferencia', 'qr', 'tarjeta'])->random();

        $extras = match ($metodo) {
            'transferencia' => [
                'numero_transaccion'    => 'TRX-' . strtoupper(bin2hex(random_bytes(4))),
                'banco_origen'          => collect(['BCP', 'Banco Unión', 'BNB', 'Banco Mercantil'])->random(),
                'titular_transferencia' => $cobro->suscripcion?->cliente?->user?->nombres ?? 'Titular',
            ],
            'qr' => [
                'codigo_qr'    => 'QR-' . rand(100000, 999999),
                'billetera_qr' => collect(['Tigo Money', 'Yape Bolivia'])->random(),
            ],
            'tarjeta' => [
                'codigo_autorizacion' => 'AUTH-' . rand(1000, 9999),
                'ultimos_4_tarjeta'   => (string) rand(1000, 9999),
                'marca_tarjeta'       => collect(['Visa', 'Mastercard'])->random(),
            ],
            'efectivo' => [
                'nombre_pagador' => $cobro->suscripcion?->cliente?->user?->nombres ?? 'Pagador',
            ],
            default => [],
        };

        return SuscripcionesPagos::create(array_merge([
            'suscripcion_cobro_id' => $cobro->id,
            'monto_pagado'         => $monto,
            'fecha_pago'           => $fecha->toDateString(),
            'metodo_pago'          => $metodo,
            'estado_verificacion'  => 'verificado',
            'observaciones'        => 'Pago demo generado por seeder',
        ], $extras));
    }

    /* ------------------------------------------------------------------ */
    /* Escenarios                                                          */
    /* ------------------------------------------------------------------ */

    private function contratoMensualTotalmentePagado(array $a): void
    {
        $inicio = now()->subMonths(6)->startOfMonth();
        $precio = 1500.00;
        $sus = $this->crearSuscripcionLimpia($a, 'mensual', $precio, $inicio, $inicio->copy()->addMonths(12));

        // 6 cobros pasados pagados completamente
        for ($i = 0; $i < 6; $i++) {
            $ini = $inicio->copy()->addMonths($i);
            $ven = $ini->copy()->addMonth()->subDay();
            $cobro = $this->crearCobro($sus, 'Mensual #' . ($i + 1), $precio, $ini, $ven);
            $this->pagar($cobro, $precio, $ven->copy()->subDays(rand(1, 7)));
        }

        // Cobro del mes actual aún no pagado (pendiente, no vencido)
        $ini = now()->startOfMonth();
        $ven = $ini->copy()->addMonth()->subDay();
        $this->crearCobro($sus, 'Mensual #7', $precio, $ini, $ven);
    }

    private function contratoMensualConMorosidad(array $a): void
    {
        $inicio = now()->subMonths(4)->startOfMonth();
        $precio = 800.00;
        $sus = $this->crearSuscripcionLimpia($a, 'mensual', $precio, $inicio, $inicio->copy()->addMonths(12));

        // Mes 1 y 2: pagados
        for ($i = 0; $i < 2; $i++) {
            $ini = $inicio->copy()->addMonths($i);
            $ven = $ini->copy()->addMonth()->subDay();
            $cobro = $this->crearCobro($sus, 'Mensual #' . ($i + 1), $precio, $ini, $ven);
            $this->pagar($cobro, $precio, $ven->copy()->subDays(rand(1, 5)), 'efectivo');
        }

        // Mes 3 y 4: vencidos sin pago (morosidad)
        for ($i = 2; $i < 4; $i++) {
            $ini = $inicio->copy()->addMonths($i);
            $ven = $ini->copy()->addMonth()->subDay();
            $this->crearCobro($sus, 'Mensual #' . ($i + 1), $precio, $ini, $ven, 'vencido');
        }
    }

    private function contratoTrimestralVencido(array $a): void
    {
        $inicio = now()->subMonths(7)->startOfMonth();
        $precio = 2800.00;
        $sus = $this->crearSuscripcionLimpia($a, 'trimestral', $precio, $inicio, $inicio->copy()->addMonths(12));

        // Trimestre 1: pago parcial
        $ini1 = $inicio->copy();
        $ven1 = $ini1->copy()->addMonths(3)->subDay();
        $cobro1 = $this->crearCobro($sus, 'Trimestral #1', $precio, $ini1, $ven1);
        $this->pagar($cobro1, $precio * 0.4, $ven1->copy()->subDays(10), 'transferencia');
        // El observer pone 'parcial' al recibir un pago < monto
        $cobro1->refresh();
        if ($cobro1->estado === 'parcial') {
            // Lo marcamos vencido porque ya pasó la fecha
            $cobro1->estado = 'vencido';
            $cobro1->save();
        }

        // Trimestre 2: vencido sin pagos
        $ini2 = $inicio->copy()->addMonths(3);
        $ven2 = $ini2->copy()->addMonths(3)->subDay();
        $this->crearCobro($sus, 'Trimestral #2', $precio, $ini2, $ven2, 'vencido');
    }

    private function contratoAnualNuevo(array $a): void
    {
        $inicio = now()->startOfMonth();
        $precio = 16800.00;
        $sus = $this->crearSuscripcionLimpia($a, 'anual', $precio, $inicio, $inicio->copy()->addYear());

        // Un único cobro anual pendiente
        $this->crearCobro($sus, 'Anual', $precio, $inicio, $inicio->copy()->addYear()->subDay());
    }

    private function contratoMensualConPagoParcial(array $a): void
    {
        $inicio = now()->subMonths(3)->startOfMonth();
        $precio = 1200.00;
        $sus = $this->crearSuscripcionLimpia($a, 'mensual', $precio, $inicio, $inicio->copy()->addMonths(12));

        // Mes 1: pagado completo con QR
        $ini = $inicio->copy();
        $ven = $ini->copy()->addMonth()->subDay();
        $cobro = $this->crearCobro($sus, 'Mensual #1', $precio, $ini, $ven);
        $this->pagar($cobro, $precio, $ven->copy()->subDays(3), 'qr');

        // Mes 2: pago parcial (60%)
        $ini = $inicio->copy()->addMonth();
        $ven = $ini->copy()->addMonth()->subDay();
        $cobro = $this->crearCobro($sus, 'Mensual #2', $precio, $ini, $ven);
        $this->pagar($cobro, $precio * 0.6, $ven->copy()->subDays(2), 'tarjeta');
        $cobro->refresh();
        if ($cobro->estado === 'parcial' && Carbon::parse($cobro->fecha_vencimiento)->isPast()) {
            $cobro->estado = 'vencido';
            $cobro->save();
        }

        // Mes 3: vencido sin pago
        $ini = $inicio->copy()->addMonths(2);
        $ven = $ini->copy()->addMonth()->subDay();
        $this->crearCobro($sus, 'Mensual #3', $precio, $ini, $ven, 'vencido');
    }

    private function contratoSemestralPagado(array $a): void
    {
        $inicio = now()->subMonths(6)->startOfMonth();
        $precio = 8250.00;
        $sus = $this->crearSuscripcionLimpia($a, 'semestral', $precio, $inicio, $inicio->copy()->addYear());

        // Semestre 1: pagado completo
        $ini = $inicio->copy();
        $ven = $ini->copy()->addMonths(6)->subDay();
        $cobro = $this->crearCobro($sus, 'Semestral #1', $precio, $ini, $ven);
        $this->pagar($cobro, $precio, $ven->copy()->subDays(15), 'transferencia');

        // Semestre 2: vigente, aún pendiente
        $ini = $inicio->copy()->addMonths(6);
        $ven = $ini->copy()->addMonths(6)->subDay();
        $this->crearCobro($sus, 'Semestral #2', $precio, $ini, $ven);
    }
}
