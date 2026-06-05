<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesTarifas;
use App\Observers\SuscripcionObserver;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuscripcionesSeeder extends Seeder
{
    public function run(): void
    {
        // Asegura que el mall (pisos y tiendas) exista antes de continuar.
        $this->call(MallDataSeeder::class);

        $alquilada = EstadoTienda::where('estado', 'Alquilada')->first();
        if (! $alquilada) {
            $this->command?->error('No existe el estado "Alquilada". Corre EstadosTiendasSeeder primero.');
            return;
        }

        // Idempotencia: comprueba si una de las marcas exclusivas de este seeder ya existe.
        $marcasPropias = collect($this->contratos())->pluck('tienda.marca')->all();
        if (Marcas::whereIn('nombre', $marcasPropias)->exists()) {
            $this->command?->info('SuscripcionesSeeder: ya fue ejecutado anteriormente, se omite.');
            return;
        }

        // Tiendas disponibles (sin cliente asignado), ordenadas por ID.
        $tiendasDisponibles = InfraestructurasTiendas::whereNull('cliente_id')
            ->orderBy('id')
            ->get();

        if ($tiendasDisponibles->count() < count($this->contratos())) {
            $this->command?->warn('No hay suficientes tiendas disponibles. Crea más tiendas primero.');
            return;
        }

        DB::transaction(function () use ($tiendasDisponibles, $alquilada) {
            $tiendaIndex = 0;

            foreach ($this->contratos() as $contrato) {
                $cliente = Clientes::whereHas(
                    'user', fn ($q) => $q->where('email', $contrato['email'])
                )->first();

                if (! $cliente) {
                    $this->command?->warn("Cliente {$contrato['email']} no encontrado, se omite.");
                    continue;
                }

                $tienda = $tiendasDisponibles[$tiendaIndex++];
                $tiendaData = $contrato['tienda'];

                // ── 1. Configurar la tienda con nombre, fotos y teléfono ──────────
                $tienda->update([
                    'nombre'              => $tiendaData['nombre'],
                    'descripcion'         => $tiendaData['descripcion'],
                    'telefono_referencia' => $tiendaData['telefono'],
                    'foto_referencial'    => $tiendaData['foto_referencial'],
                    'vitrina_1'           => $tiendaData['vitrina_1'],
                    'vitrina_2'           => $tiendaData['vitrina_2'],
                    'vitrina_3'           => $tiendaData['vitrina_3'],
                ]);

                // ── 2. Crear marca del cliente y asociarla a la tienda ────────────
                $marca = Marcas::firstOrCreate(
                    ['nombre' => $tiendaData['marca'], 'cliente_id' => $cliente->id],
                    [
                        'descripcion' => $tiendaData['marca_descripcion'],
                        'estado'      => 'activo',
                    ]
                );
                $tienda->marcas()->syncWithoutDetaching([$marca->id]);

                // ── 3. Calcular precio según tarifas vigentes ─────────────────────
                $calc = SuscripcionesTarifas::calcularAlquiler(
                    (float) $tienda->tamano,
                    $contrato['meses']
                );
                $precio = $calc['precio_total_con_descuento'] > 0
                    ? $calc['precio_total_con_descuento']
                    : $contrato['precio_fallback'];

                $fechaInicio = Carbon::parse($contrato['fecha_inicio']);
                $fechaFin    = $fechaInicio->copy()->addMonthsNoOverflow($contrato['meses'])->subDay();

                // ── 4. Crear suscripción ──────────────────────────────────────────
                // El hook booted() de Suscripciones llama automáticamente a
                // generarCobrosMensuales(), que crea todos los cobros del período.
                $suscripcion = Suscripciones::create([
                    'cliente_id'                  => $cliente->id,
                    'marca_id'                    => $marca->id,
                    'tipo'                        => $contrato['tipo'],
                    'precio'                      => $precio,
                    'fecha_inicio'                => $fechaInicio->toDateString(),
                    'fecha_fin'                   => $fechaFin->toDateString(),
                    'infraestructuras_tienda_id'  => $tienda->id,
                    'infraestructuras_piso_id'    => $tienda->infraestructura_piso_id,
                ]);

                // ── 5. Sincronizar estado de la tienda ────────────────────────────
                // El observer marca la tienda como "Alquilada" si el contrato
                // está vigente hoy, o "Disponible" si ya venció.
                SuscripcionObserver::syncTienda($tienda->id);

                $cobrosGenerados = $suscripcion->cobros()->count();
                $this->command?->line(
                    "  ✓ {$cliente->user?->nombres} → {$tienda->nombre} | {$contrato['tipo']} | ".
                    "{$fechaInicio->format('d/m/Y')} – {$fechaFin->format('d/m/Y')} | ".
                    "{$cobrosGenerados} cobros generados"
                );
            }
        });

        $this->command?->info('SuscripcionesSeeder: contratos creados y cobros generados automáticamente.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Definición de contratos
    // Fechas variadas entre 2024 y 2026 para probar generación de cobros.
    // Los contratos 1-4 ya vencieron; 5-6 están activos (hoy ≈ jun-2026).
    // ─────────────────────────────────────────────────────────────────────────
    private function contratos(): array
    {
        return [
            // ── Contrato 1: 1 año – vencido (2024) ──────────────────────────
            [
                'email'          => 'cliente@prueba.com',
                'tipo'           => '1 año',
                'meses'          => 12,
                'fecha_inicio'   => '2024-01-15',
                'precio_fallback' => 18000.00,
                'tienda' => [
                    'nombre'          => 'TechZone Pro',
                    'descripcion'     => 'Venta y reparación de dispositivos electrónicos y accesorios premium.',
                    'telefono'        => '+591 77100001',
                    'marca'           => 'TechZone',
                    'marca_descripcion' => 'Tecnología, reparación y accesorios para dispositivos móviles.',
                    'foto_referencial' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800&auto=format&fit=crop&q=80',
                    'vitrina_1'       => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600&auto=format&fit=crop&q=80',
                    'vitrina_2'       => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=600&auto=format&fit=crop&q=80',
                    'vitrina_3'       => 'https://images.unsplash.com/photo-1563770660941-20978e870e26?w=600&auto=format&fit=crop&q=80',
                ],
            ],

            // ── Contrato 2: 6 meses – vencido (2024) ────────────────────────
            [
                'email'          => 'mateo.demo@mall.com',
                'tipo'           => '6 meses',
                'meses'          => 6,
                'fecha_inicio'   => '2024-06-01',
                'precio_fallback' => 7200.00,
                'tienda' => [
                    'nombre'          => 'Mateo Sport',
                    'descripcion'     => 'Ropa deportiva y calzado para actividades de alto rendimiento.',
                    'telefono'        => '+591 77100002',
                    'marca'           => 'M-Sport',
                    'marca_descripcion' => 'Moda deportiva, indumentaria técnica y calzado running.',
                    'foto_referencial' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=800&auto=format&fit=crop&q=80',
                    'vitrina_1'       => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
                    'vitrina_2'       => 'https://images.unsplash.com/photo-1556906781-9a412961a28e?w=600&auto=format&fit=crop&q=80',
                    'vitrina_3'       => 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600&auto=format&fit=crop&q=80',
                ],
            ],

            // ── Contrato 3: 3 meses – vencido (2025) ────────────────────────
            [
                'email'          => 'sofia.demo@mall.com',
                'tipo'           => '3 meses',
                'meses'          => 3,
                'fecha_inicio'   => '2025-02-01',
                'precio_fallback' => 3900.00,
                'tienda' => [
                    'nombre'          => "Sofía's Closet",
                    'descripcion'     => 'Moda femenina exclusiva con diseños de autor y prendas importadas.',
                    'telefono'        => '+591 77100003',
                    'marca'           => "Sofía's",
                    'marca_descripcion' => 'Boutique de moda femenina, prendas selectas y accesorios.',
                    'foto_referencial' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&auto=format&fit=crop&q=80',
                    'vitrina_1'       => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=600&auto=format&fit=crop&q=80',
                    'vitrina_2'       => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600&auto=format&fit=crop&q=80',
                    'vitrina_3'       => 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=600&auto=format&fit=crop&q=80',
                ],
            ],

            // ── Contrato 4: 1 año – vencido (abr-2026) ──────────────────────
            [
                'email'          => 'diego.demo@mall.com',
                'tipo'           => '1 año',
                'meses'          => 12,
                'fecha_inicio'   => '2025-04-01',
                'precio_fallback' => 16800.00,
                'tienda' => [
                    'nombre'          => 'Diego Gourmet',
                    'descripcion'     => 'Delicatessen con productos gourmet, importados y vinos selectos.',
                    'telefono'        => '+591 77100004',
                    'marca'           => 'Gourmet D',
                    'marca_descripcion' => 'Tienda gourmet: quesos, embutidos, conservas y vinos de autor.',
                    'foto_referencial' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&auto=format&fit=crop&q=80',
                    'vitrina_1'       => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&auto=format&fit=crop&q=80',
                    'vitrina_2'       => 'https://images.unsplash.com/photo-1502164980785-f8aa41d53611?w=600&auto=format&fit=crop&q=80',
                    'vitrina_3'       => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&auto=format&fit=crop&q=80',
                ],
            ],

            // ── Contrato 5: 6 meses – ACTIVO (ene-2026 → jul-2026) ──────────
            [
                'email'          => 'camila.demo@mall.com',
                'tipo'           => '6 meses',
                'meses'          => 6,
                'fecha_inicio'   => '2026-01-10',
                'precio_fallback' => 7800.00,
                'tienda' => [
                    'nombre'          => 'Cami Café',
                    'descripcion'     => 'Cafetería artesanal con granos de origen, repostería casera y ambiente acogedor.',
                    'telefono'        => '+591 77100005',
                    'marca'           => 'Cami',
                    'marca_descripcion' => 'Café de especialidad, infusiones y repostería artesanal.',
                    'foto_referencial' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=800&auto=format&fit=crop&q=80',
                    'vitrina_1'       => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&auto=format&fit=crop&q=80',
                    'vitrina_2'       => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&auto=format&fit=crop&q=80',
                    'vitrina_3'       => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=600&auto=format&fit=crop&q=80',
                ],
            ],

            // ── Contrato 6: 1 año – ACTIVO (mar-2026 → feb-2027) ────────────
            [
                'email'          => 'joaquin.demo@mall.com',
                'tipo'           => '1 año',
                'meses'          => 12,
                'fecha_inicio'   => '2026-03-01',
                'precio_fallback' => 15600.00,
                'tienda' => [
                    'nombre'          => 'Flores & Arte',
                    'descripcion'     => 'Galería y tienda de arte boliviano contemporáneo, artesanías y objetos de diseño.',
                    'telefono'        => '+591 77100006',
                    'marca'           => 'Arte Flores',
                    'marca_descripcion' => 'Arte contemporáneo, artesanías únicas y diseño decorativo boliviano.',
                    'foto_referencial' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800&auto=format&fit=crop&q=80',
                    'vitrina_1'       => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=600&auto=format&fit=crop&q=80',
                    'vitrina_2'       => 'https://images.unsplash.com/photo-1531913764164-f85c52e6e654?w=600&auto=format&fit=crop&q=80',
                    'vitrina_3'       => 'https://images.unsplash.com/photo-1548438294-1ad5d5f4f063?w=600&auto=format&fit=crop&q=80',
                ],
            ],
        ];
    }
}
