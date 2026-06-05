<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\Marcas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PagosSeeder extends Seeder
{
    public function run(): void
    {
        // Garantiza que existan infraestructura, tiendas y suscripciones
        $this->call(SuscripcionesSeeder::class);

        // Idempotencia: si la marca "TechZone" ya tiene pagos, este seeder ya corrió
        $marcaId = Marcas::where('nombre', 'TechZone')->value('id');
        if ($marcaId) {
            $susId = Suscripciones::where('marca_id', $marcaId)->value('id');
            if ($susId && SuscripcionesPagos::whereHas('cobro', fn ($q) => $q->where('suscripcion_id', $susId))->exists()) {
                $this->command?->info('PagosSeeder: ya fue ejecutado anteriormente, se omite.');
                return;
            }
        }

        DB::transaction(function () {
            foreach ($this->escenarios() as $escenario) {
                $this->ejecutarEscenario($escenario);
            }
        });

        // Marcar como "vencido" los cobros pasados que quedaron sin pagar
        Artisan::call('cobros:marcar-vencidos');
        $this->command?->info('PagosSeeder completado. Cobros morosos marcados como vencidos.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Plan de pago por suscripción
    // Acciones por índice de cobro (0 = primer cobro generado por el sistema):
    //   'full'    → pago completo en fecha ±5 días
    //   'late'    → pago completo pero tardío (6-14 días después del vencimiento)
    //   'partial' → pago parcial (% indicado en 'partial_pct') → el sistema crea
    //               el cobro de saldo pendiente → el seeder lo paga si ya venció
    //   'skip'    → se deja sin pagar (queda como moroso)
    // ─────────────────────────────────────────────────────────────────────────
    private function escenarios(): array
    {
        return [
            // ── TechZone Pro: 1 año expirado (ene 2024 – ene 2025) ─────────
            // 12 cobros: en general al día, uno parcial (cobro 4), uno tardío (cobro 8)
            [
                'email' => 'cliente@prueba.com',
                'marca' => 'TechZone',
                'plan'  => [
                    0 => 'full',  1 => 'full',  2 => 'full',  3 => 'full',
                    4 => 'partial',              // 65% → sistema crea saldo pendiente → se paga
                    5 => 'full',  6 => 'full',  7 => 'full',
                    8 => 'late',                 // pagado con retraso
                    9 => 'full', 10 => 'full', 11 => 'full',
                ],
                'partial_pct' => [4 => 0.65],
            ],

            // ── Mateo Sport: 6 meses expirado (jun – nov 2024) ────────────
            // 6 cobros: uno parcial (cobro 3), resto al día
            [
                'email' => 'mateo.demo@mall.com',
                'marca' => 'M-Sport',
                'plan'  => [
                    0 => 'full', 1 => 'full', 2 => 'full',
                    3 => 'partial',             // 70% → saldo pagado
                    4 => 'full', 5 => 'full',
                ],
                'partial_pct' => [3 => 0.70],
            ],

            // ── Sofía's Closet: 3 meses expirado (feb – abr 2025) ─────────
            // 3 cobros: todos pagados en tiempo
            [
                'email' => 'sofia.demo@mall.com',
                'marca' => "Sofía's",
                'plan'  => [0 => 'full', 1 => 'full', 2 => 'full'],
            ],

            // ── Diego Gourmet: 1 año expirado (abr 2025 – mar 2026) ───────
            // 12 cobros: uno parcial (cobro 6), uno tardío (cobro 10), resto al día
            [
                'email' => 'diego.demo@mall.com',
                'marca' => 'Gourmet D',
                'plan'  => [
                    0 => 'full', 1 => 'full', 2 => 'full', 3 => 'full', 4 => 'full', 5 => 'full',
                    6 => 'partial',             // 55% → saldo pagado
                    7 => 'full', 8 => 'full', 9 => 'full',
                    10 => 'late',               // tardío
                    11 => 'full',
                ],
                'partial_pct' => [6 => 0.55],
            ],

            // ── Cami Café: 6 meses ACTIVO (ene 2026 – jul 2026) ───────────
            // 5 cobros pasados (ene–may 2026): uno parcial (cobro 2),
            // cobro 4 (mayo) queda moroso
            [
                'email' => 'camila.demo@mall.com',
                'marca' => 'Cami',
                'plan'  => [
                    0 => 'full', 1 => 'full',
                    2 => 'partial',             // 75% → saldo pagado (venció en el pasado)
                    3 => 'full',
                    4 => 'skip',                // MOROSO: cobro de mayo 2026
                ],
                'partial_pct' => [2 => 0.75],
            ],

            // ── Flores & Arte: 1 año ACTIVO (mar 2026 – feb 2027) ─────────
            // 4 cobros pasados (mar–jun 2026):
            // cobros 2 y 3 quedan morosos (mayo y junio)
            [
                'email' => 'joaquin.demo@mall.com',
                'marca' => 'Arte Flores',
                'plan'  => [
                    0 => 'full', 1 => 'full',
                    2 => 'skip',               // MOROSO: cobro de mayo 2026
                    3 => 'skip',               // MOROSO: cobro de junio 2026
                ],
            ],
        ];
    }

    private function ejecutarEscenario(array $escenario): void
    {
        $cliente = Clientes::whereHas('user', fn ($q) => $q->where('email', $escenario['email']))->first();
        if (! $cliente) {
            $this->command?->warn("  ✗ Cliente {$escenario['email']} no encontrado.");
            return;
        }

        $marcaId = Marcas::where('nombre', $escenario['marca'])
            ->where('cliente_id', $cliente->id)
            ->value('id');

        $suscripcion = $marcaId
            ? Suscripciones::where('cliente_id', $cliente->id)->where('marca_id', $marcaId)->first()
            : null;

        if (! $suscripcion) {
            $this->command?->warn("  ✗ Suscripción '{$escenario['marca']}' no encontrada.");
            return;
        }

        // Solo cobros generados automáticamente por el sistema (no parciales de saldo)
        $cobros = $suscripcion->cobros()
            ->where('es_parcial', false)
            ->orderBy('fecha_vencimiento')
            ->get();

        $plan       = $escenario['plan'] ?? [];
        $partialPct = $escenario['partial_pct'] ?? [];
        $pagosCreados = 0;

        foreach ($cobros as $index => $cobro) {
            // No procesar cobros futuros
            if (Carbon::parse($cobro->fecha_vencimiento)->isFuture()) {
                break;
            }

            $accion = $plan[$index] ?? 'skip';
            if ($accion === 'skip') {
                continue;
            }

            $esLate     = ($accion === 'late');
            $fechaPago  = $this->fechaPago($cobro->fecha_vencimiento, $esLate);
            $metodo     = $this->metodoAleatorio();

            if ($accion === 'partial') {
                $pct          = $partialPct[$index] ?? 0.65;
                $montoParcial = round((float) $cobro->monto * $pct, 2);

                // Pago parcial → el helper crea el cobro de saldo pendiente
                $cobroResto = $this->pagarCobro($cobro, $montoParcial, $fechaPago, $metodo);
                $pagosCreados++;

                // Si el cobro de saldo pendiente ya venció, pagarlo también
                if ($cobroResto && Carbon::parse($cobroResto->fecha_vencimiento)->isPast()) {
                    $fechaResto = $this->fechaPago($cobroResto->fecha_vencimiento, false);
                    $this->pagarCobro($cobroResto, $cobroResto->monto, $fechaResto, $this->metodoAleatorio());
                    $pagosCreados++;
                }
            } else {
                // 'full' o 'late'
                $this->pagarCobro($cobro, (float) $cobro->monto, $fechaPago, $metodo);
                $pagosCreados++;
            }
        }

        $morosos = $suscripcion->cobros()
            ->whereNotIn('estado', ['pagado', 'anulado'])
            ->whereDate('fecha_vencimiento', '<', now())
            ->count();

        $this->command?->line(
            "  ✓ {$escenario['marca']} | {$pagosCreados} pago(s) creados".
            ($morosos > 0 ? " | {$morosos} cobro(s) moroso(s)" : ' | suscripción al día')
        );
    }

    /**
     * Crea el pago y aplica la lógica de división de saldo parcial,
     * replicando exactamente lo que hace SuscripcionCustomController::storePayment().
     *
     * Si el pago es parcial:
     *   1. Crea el cobro de saldo pendiente (es_parcial = true)
     *   2. Ajusta el monto del cobro original al monto pagado
     *   3. Marca ambos registros como 'pagado'
     *   4. Retorna el nuevo cobro para que el caller pueda pagarlo si venció
     *
     * Si el pago es completo, retorna null.
     */
    private function pagarCobro(SuscripcionesCobros $cobro, float $monto, Carbon $fecha, string $metodo): ?SuscripcionesCobros
    {
        $cobro->refresh();

        $pago = SuscripcionesPagos::create(array_merge([
            'suscripcion_cobro_id' => $cobro->id,
            'monto_pagado'         => $monto,
            'fecha_pago'           => $fecha->toDateString(),
            'metodo_pago'          => $metodo,
            'estado_verificacion'  => 'verificado',
            'monto_total'          => $cobro->monto,
            'observaciones'        => 'Pago registrado por administración.',
        ], $this->extrasPorMetodo($metodo, $cobro)));

        $totalPagado = SuscripcionesPagos::where('suscripcion_cobro_id', $cobro->id)->sum('monto_pagado');
        $saldo       = max(0, round((float) $cobro->monto - (float) $totalPagado, 2));

        if ($saldo > 0.01) {
            // Pago parcial: el sistema crea el cobro de saldo pendiente
            $cobroResto = SuscripcionesCobros::create([
                'suscripcion_id'    => $cobro->suscripcion_id,
                'concepto'          => SuscripcionesCobros::conceptoSaldoPendiente($cobro),
                'monto'             => $saldo,
                'fecha_inicio'      => $fecha->toDateString(),
                'fecha_vencimiento' => SuscripcionesCobros::fechaSaldoPendiente($cobro),
                'estado'            => 'pendiente',
                'observaciones'     => 'Saldo pendiente generado por pago parcial.',
                'es_parcial'        => true,
            ]);

            $cobro->update([
                'monto'           => round((float) $totalPagado, 2),
                'saldo_pendiente' => 0,
                'estado'          => 'pagado',
            ]);
            $pago->update(['pago_pendiente' => 0, 'estado_snapshot' => 'pagado']);

            return $cobroResto;
        }

        $cobro->update(['saldo_pendiente' => 0, 'estado' => 'pagado']);
        $pago->update(['pago_pendiente' => 0, 'estado_snapshot' => 'pagado']);

        return null;
    }

    private function fechaPago(string $fechaVencimiento, bool $late): Carbon
    {
        $base   = Carbon::parse($fechaVencimiento);
        $offset = $late ? rand(6, 14) : rand(-5, 5);
        $fecha  = $base->copy()->addDays($offset);

        // Nunca fecha futura
        return $fecha->isFuture() ? Carbon::yesterday() : $fecha;
    }

    private function metodoAleatorio(): string
    {
        return collect(['efectivo', 'transferencia', 'qr', 'tarjeta'])->random();
    }

    private function extrasPorMetodo(string $metodo, SuscripcionesCobros $cobro): array
    {
        $user    = $cobro->suscripcion?->cliente?->user;
        $titular = $user ? trim("{$user->nombres} {$user->apellido_paterno}") : 'Titular';

        return match ($metodo) {
            'efectivo' => [
                'nombre_pagador' => 'Administración',
            ],
            'transferencia' => [
                'numero_transaccion'    => 'TRX-' . strtoupper(bin2hex(random_bytes(4))),
                'banco_origen'          => collect(['Banco Unión', 'BCP', 'BNB', 'Ganadero', 'BISA'])->random(),
                'titular_transferencia' => $titular,
            ],
            'qr' => [
                'codigo_qr'    => 'QR-' . rand(100000, 999999),
                'billetera_qr' => collect(['Tigo Money', 'Yape', 'Soli'])->random(),
            ],
            'tarjeta' => [
                'codigo_autorizacion' => 'AUTH-' . rand(1000, 9999),
                'ultimos_4_tarjeta'   => str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT),
                'marca_tarjeta'       => collect(['Visa', 'Mastercard'])->random(),
            ],
            default => [],
        };
    }
}
