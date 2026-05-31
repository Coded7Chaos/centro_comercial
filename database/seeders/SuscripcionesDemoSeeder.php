<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\ProductosImagenes;
use App\Models\Categorias;
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

            $datosTiendas = [
                [
                    'nombre' => 'Nébula Coffee',
                    'desc' => 'Café de especialidad en las alturas con granos seleccionados.',
                    'telefono' => '+591 72200001',
                    'brand' => 'Nébula',
                    'brand_desc' => 'Cafetería de especialidad y pastelería fina.',
                    'subcat' => 'Café & Pastelería',
                    'products' => [
                        [
                            'nombre' => 'Café Espresso Doble',
                            'desc' => 'Extracción intensa de granos de café de origen 100% Arábica.',
                            'precio' => 18.00,
                            'img' => 'https://images.unsplash.com/photo-1510972527409-cef7e2b247f9?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Cappuccino Vainilla',
                            'desc' => 'Café espresso con leche vaporizada sedosa y un toque de vainilla.',
                            'precio' => 22.00,
                            'img' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Tarta Rústica de Chocolate',
                            'desc' => 'Porción de tarta de chocolate húmeda con ganache de cacao al 70%.',
                            'precio' => 25.00,
                            'img' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80'
                        ]
                    ]
                ],
                [
                    'nombre' => 'Pixel Arcade',
                    'desc' => 'Sala arcade retro con las mejores máquinas recreativas de los 90s.',
                    'telefono' => '+591 72200002',
                    'brand' => 'Pixel Arcade',
                    'brand_desc' => 'Entretenimiento gaming retro y moderno.',
                    'subcat' => 'Videojuegos & Consolas',
                    'products' => [
                        [
                            'nombre' => 'Pase de Juego - 1 Hora',
                            'desc' => 'Acceso ilimitado a todas las máquinas arcade y consolas retro por 60 minutos.',
                            'precio' => 40.00,
                            'img' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Ficha Virtual x10',
                            'desc' => 'Paquete de 10 créditos para máquinas de gacha y simuladores avanzados.',
                            'precio' => 25.00,
                            'img' => 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Torneo Retro Entry',
                            'desc' => 'Inscripción para los torneos semanales de Street Fighter o Pacman.',
                            'precio' => 30.00,
                            'img' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=600&auto=format&fit=crop&q=80'
                        ]
                    ]
                ],
                [
                    'nombre' => 'Zen Sushi',
                    'desc' => 'Cocina nipona moderna y refinada en un ambiente minimalista.',
                    'telefono' => '+591 72200003',
                    'brand' => 'Zen Sushi',
                    'brand_desc' => 'Alta cocina japonesa y mariscos frescos.',
                    'subcat' => 'Comida Asiática',
                    'products' => [
                        [
                            'nombre' => 'Combo Roll Zen x12',
                            'desc' => 'Selección premium de 12 piezas de sushi (Maki, Uramaki y Nigiri).',
                            'precio' => 75.00,
                            'img' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Ramen Tonkotsu Clásico',
                            'desc' => 'Caldo concentrado de cerdo, fideos artesanales, chashu, huevo marinado y nori.',
                            'precio' => 55.00,
                            'img' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Tempura Mixto de Langostinos',
                            'desc' => 'Vegetales y langostinos gigantes fritos en tempura extra crujiente.',
                            'precio' => 48.00,
                            'img' => 'https://images.unsplash.com/photo-1615361413125-147e4c76f264?w=600&auto=format&fit=crop&q=80'
                        ]
                    ]
                ],
                [
                    'nombre' => 'Luna Bar',
                    'desc' => 'Cócteles de autor con la mejor vista panorámica nocturna.',
                    'telefono' => '+591 72200004',
                    'brand' => 'Luna',
                    'brand_desc' => 'Mixología y coctelería fina en el rooftop del mall.',
                    'subcat' => 'Bares & Coctelería',
                    'products' => [
                        [
                            'nombre' => 'Mojito de Maracuyá',
                            'desc' => 'Ron premium, menta fresca, limón, pulpa de maracuyá y soda.',
                            'precio' => 35.00,
                            'img' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Gin Tonic Botánico',
                            'desc' => 'Ginebra destilada, agua tónica premium, frutos rojos y cardamomo.',
                            'precio' => 45.00,
                            'img' => 'https://images.unsplash.com/photo-1570598912132-0ba1dd952b7d?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Tabla de Quesos Selectos',
                            'desc' => 'Variedad de quesos curados maduros servidos con frutos secos y miel de abejas.',
                            'precio' => 60.00,
                            'img' => 'https://images.unsplash.com/photo-1486427944299-d1955d23e317?w=600&auto=format&fit=crop&q=80'
                        ]
                    ]
                ],
                [
                    'nombre' => 'Circuit Lab',
                    'desc' => 'Gadgets, audio y accesorios de última tecnología.',
                    'telefono' => '+591 72200005',
                    'brand' => 'Circuit',
                    'brand_desc' => 'Tecnología móvil, cargadores y periféricos premium.',
                    'subcat' => 'Accesorios & Gadgets',
                    'products' => [
                        [
                            'nombre' => 'Cargador Inalámbrico Rápido 15W',
                            'desc' => 'Base de carga rápida magnética compatible con dispositivos iOS y Android.',
                            'precio' => 120.00,
                            'img' => 'https://images.unsplash.com/photo-1622445262465-2481c4574875?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Auriculares Bluetooth Pro ANC',
                            'desc' => 'Auriculares in-ear con cancelación activa de ruido y 24 horas de batería.',
                            'precio' => 380.00,
                            'img' => 'https://images.unsplash.com/photo-1608156639585-b3a032ef9689?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Soporte de Auto MagSafe',
                            'desc' => 'Soporte magnético premium para rejilla de ventilación de auto.',
                            'precio' => 90.00,
                            'img' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=600&auto=format&fit=crop&q=80'
                        ]
                    ]
                ],
                [
                    'nombre' => 'Velvet Shoes',
                    'desc' => 'Calzado artesanal premium de cuero para hombres y mujeres.',
                    'telefono' => '+591 72200006',
                    'brand' => 'Velvet',
                    'brand_desc' => 'Zapatería fina y manufactura en cueros genuinos.',
                    'subcat' => 'Calzado Premium',
                    'products' => [
                        [
                            'nombre' => 'Botas de Cuero Oxford',
                            'desc' => 'Botas de vestir de cuero curtido vegetal con costuras de alta durabilidad.',
                            'precio' => 520.00,
                            'img' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Mocasines de Gamuza Café',
                            'desc' => 'Calzado liviano y flexible sin cordones en fina gamuza marrón.',
                            'precio' => 380.00,
                            'img' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&auto=format&fit=crop&q=80'
                        ],
                        [
                            'nombre' => 'Tacones Clásicos Charol',
                            'desc' => 'Zapatos de tacón alto en charol negro pulido para ocasiones formales.',
                            'precio' => 450.00,
                            'img' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=600&auto=format&fit=crop&q=80'
                        ]
                    ]
                ]
            ];

            // Asignar cada tienda a un cliente, marcarla como Alquilada y crearle una marca y productos coherentes
            $asignaciones = [];
            foreach ($clientes as $i => $cliente) {
                $tienda = $tiendas[$i];
                $storeData = $datosTiendas[$i];

                $tienda->cliente_id = $cliente->id;
                $tienda->id_estado  = $alquilada->id;
                $tienda->nombre = $storeData['nombre'];
                $tienda->descripcion = $storeData['desc'];
                $tienda->telefono_referencia = $storeData['telefono'];
                $tienda->save();

                // Crear marca del cliente
                $marca = Marcas::create([
                    'nombre'      => $storeData['brand'],
                    'cliente_id'  => $cliente->id,
                    'descripcion' => $storeData['brand_desc'],
                    'estado'      => 'activo',
                ]);

                $tienda->marcas()->attach($marca->id);

                // Obtener subcategoría por nombre
                $subcat = Categorias::where('nombre', $storeData['subcat'])->first();
                $categoriaId = $subcat ? $subcat->id : 1; // fallback

                // Crear 3 productos para la tienda
                foreach ($storeData['products'] as $prodData) {
                    $prod = Productos::create([
                        'nombre' => $prodData['nombre'],
                        'descripcion' => $prodData['desc'],
                        'precio' => $prodData['precio'],
                        'categoria_id' => $categoriaId,
                        'marca_id' => $marca->id,
                        'estado' => 'activo',
                        'infraestructuras_tienda_id' => $tienda->id,
                    ]);

                    ProductosImagenes::create([
                        'producto_id' => $prod->id,
                        'url' => $prodData['img'],
                        'tipo' => 'principal',
                    ]);
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
        $emails = [
            'cliente@prueba.com',
            'mateo.demo@mall.com',
            'sofia.demo@mall.com',
            'diego.demo@mall.com',
            'camila.demo@mall.com',
            'joaquin.demo@mall.com',
        ];

        $clientes = [];
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if ($user && $user->cliente) {
                $cliente = $user->cliente;
                $cliente->setRelation('user', $user);
                $clientes[] = $cliente;
            }
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
