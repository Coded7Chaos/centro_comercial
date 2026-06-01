<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\ProductosImagenes;
use App\Models\Categorias;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NotificationTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Find or create user Sofia
            $user = User::firstOrCreate(
                ['email' => 'sofia.demo@mall.com'],
                [
                    'nombres' => 'Sofía',
                    'apellido_paterno' => 'Suárez',
                    'apellido_materno' => 'Aliaga',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if (!$user->hasRole('cliente')) {
                $user->assignRole('cliente');
            }

            // Find or create Client
            $cliente = Clientes::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'ci' => '7001003',
                    'numero_celular' => '76543210',
                    'genero' => 'femenino',
                    'codigo_pais' => '+591',
                ]
            );

            // Find her shop or assign an available one
            $tienda = InfraestructurasTiendas::where('cliente_id', $cliente->id)->first();
            if (!$tienda) {
                $tienda = InfraestructurasTiendas::whereNull('cliente_id')->first();
                if (!$tienda) {
                    // Create one if none is available
                    $piso = \App\Models\InfraestructurasPisos::first();
                    $tienda = InfraestructurasTiendas::create([
                        'numero' => 777,
                        'tamano' => '100',
                        'id_estado' => \App\Models\EstadoTienda::where('estado', 'Alquilada')->first()?->id ?? 2,
                        'cliente_id' => $cliente->id,
                        'infraestructura_piso_id' => $piso->id ?? 1,
                        'nombre' => 'Tienda de Sofía',
                        'descripcion' => 'Tienda de prueba de notificaciones de Sofía.',
                        'telefono_referencia' => '+591 76543210',
                    ]);
                } else {
                    $tienda->update([
                        'cliente_id' => $cliente->id,
                        'id_estado' => \App\Models\EstadoTienda::where('estado', 'Alquilada')->first()?->id ?? 2,
                        'nombre' => 'Tienda de Sofía',
                        'descripcion' => 'Tienda de prueba de notificaciones de Sofía.',
                        'telefono_referencia' => '+591 76543210',
                    ]);
                }
            } else {
                if (!$tienda->nombre) {
                    $tienda->update([
                        'nombre' => 'Tienda de Sofía',
                        'descripcion' => 'Tienda de prueba de notificaciones de Sofía.',
                        'telefono_referencia' => '+591 76543210',
                    ]);
                }
            }

            // Find or create brand
            $marca = Marcas::firstOrCreate(
                ['nombre' => 'Marca Sofía'],
                [
                    'cliente_id' => $cliente->id,
                    'descripcion' => 'Marca de Sofía Suárez',
                    'estado' => 'activo',
                ]
            );

            if (!$tienda->marcas()->where('marca_id', $marca->id)->exists()) {
                $tienda->marcas()->attach($marca->id);
            }

            // Create products for this tienda if it doesn't have any
            if ($tienda->productos()->count() === 0) {
                $subcat = Categorias::where('nombre', 'Comida Asiática')->first();
                $categoriaId = $subcat ? $subcat->id : 1;

                $productsData = [
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
                        'img' => 'https://images.unsplash.com/photo-1553621042-f6e147245754?w=600&auto=format&fit=crop&q=80'
                    ]
                ];

                foreach ($productsData as $prodData) {
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
            }

            // Clean up existing subscriptions for Sofia
            $subs = Suscripciones::where('cliente_id', $cliente->id)->get();
            foreach ($subs as $sub) {
                foreach ($sub->cobros as $cob) {
                    $cob->pagos()->delete();
                    $cob->delete();
                }
                $sub->delete();
            }

            // Scenario 1: Vence Hoy (Iniciado hace 1 mes, vence hoy)
            $inicio1 = now()->subMonth()->startOfDay();
            $fin1 = $inicio1->copy()->addYear();
            $sus1 = Suscripciones::create([
                'cliente_id' => $cliente->id,
                'marca_id' => $marca->id,
                'infraestructuras_tienda_id' => $tienda->id,
                'tipo' => 'mensual',
                'precio' => 1200.00,
                'fecha_inicio' => $inicio1->toDateString(),
                'fecha_fin' => $fin1->toDateString(),
            ]);
            $sus1->cobros()->delete(); // Clear auto-created cobro from observer

            SuscripcionesCobros::create([
                'suscripcion_id' => $sus1->id,
                'concepto' => 'Alquiler Local - Vence Hoy',
                'monto' => 1200.00,
                'fecha_inicio' => $inicio1->toDateString(),
                'fecha_vencimiento' => now()->toDateString(), // Vence hoy
                'estado' => 'pendiente',
                'observaciones' => 'Cobro de prueba de vencimiento hoy',
            ]);

            // Scenario 2: Vence en 5 días (Iniciado hace 1 mes, vence en 5 días)
            $inicio2 = now()->subMonth()->addDays(5)->startOfDay();
            $fin2 = $inicio2->copy()->addYear();
            $sus2 = Suscripciones::create([
                'cliente_id' => $cliente->id,
                'marca_id' => $marca->id,
                'infraestructuras_tienda_id' => $tienda->id,
                'tipo' => 'mensual',
                'precio' => 1500.00,
                'fecha_inicio' => $inicio2->toDateString(),
                'fecha_fin' => $fin2->toDateString(),
            ]);
            $sus2->cobros()->delete(); // Clear auto-created cobro from observer

            SuscripcionesCobros::create([
                'suscripcion_id' => $sus2->id,
                'concepto' => 'Alquiler Local - Vence en 5 días',
                'monto' => 1500.00,
                'fecha_inicio' => $inicio2->toDateString(),
                'fecha_vencimiento' => now()->addDays(5)->toDateString(), // Vence en 5 días
                'estado' => 'pendiente',
                'observaciones' => 'Cobro de prueba de vencimiento en 5 días',
            ]);
        });
    }
}
