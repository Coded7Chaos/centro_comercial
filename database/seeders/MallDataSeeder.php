<?php

namespace Database\Seeders;

use App\Models\Categorias;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Productos;
use App\Models\ProductosImagenes;
use App\Models\Marcas;
use App\Models\Clientes;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MallDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 0. Crear un usuario y cliente para marcas/tiendas por defecto
            $user = User::firstOrCreate(['email' => 'admin@admin.com'], [
                'nombres' => 'Administrador',
                'apellido_paterno' => 'Mall',
                'apellido_materno' => 'Global',
                'password' => Hash::make('password'),
            ]);

            $cliente = Clientes::firstOrCreate(['user_id' => $user->id], [
                'ci' => '0000000',
                'numero_celular' => '00000000',
                'genero' => 'masculino',
            ]);

            // 1. Crear Categoria General para productos
            $catGral = Categorias::firstOrCreate(['nombre' => 'General'], [
                'descripcion' => 'Categoría general por defecto',
                'estado' => 'activo',
                'tipo' => 'categoria',
            ]);

            // 1.1 Crear Marca General para productos
            $marcaGral = Marcas::firstOrCreate(['nombre' => 'General'], [
                'cliente_id' => $cliente->id,
                'descripcion' => 'Marca general por defecto',
                'estado' => 'activo',
            ]);

            // 2. Crear Infraestructura (idempotente por nombre)
            $infra = Infraestructuras::firstOrCreate(
                ['nombre' => 'Marble Galleria'],
                [
                    'ubicacion' => 'Distrito Central, Av. Principal #123',
                    'lat'       => '-16.5000',
                    'long'      => '-68.1500',
                    'pisos'     => 4,
                ]
            );

            // Si esta infraestructura ya tiene pisos cargados, asumimos que el seed ya corrió.
            if ($infra->pisosInfraestructura()->exists()) {
                return;
            }

            $mallData = [
                [
                    'level' => 3,
                    'name' => 'Sky Lounge',
                    'stores' => [
                        ['name' => 'Nébula Coffee', 'desc' => 'Café de especialidad en las alturas.'],
                        ['name' => 'Pixel Arcade', 'desc' => 'Sala arcade retro con las mejores máquinas.'],
                        ['name' => 'Zen Sushi', 'desc' => 'Cocina nipona moderna y refinada.'],
                        ['name' => 'Luna Bar', 'desc' => 'Cócteles de autor con vista a la ciudad.'],
                    ]
                ],
                [
                    'level' => 2,
                    'name' => 'Tech Plaza',
                    'stores' => [
                        ['name' => 'Circuit Lab', 'desc' => 'Gadgets y accesorios de última generación.'],
                        ['name' => 'Droid Shop', 'desc' => 'Robótica y drones para aficionados y pros.'],
                        ['name' => 'Game Vault', 'desc' => 'Todo en videojuegos y consolas.'],
                        ['name' => 'Phone Galaxy', 'desc' => 'Móviles de última generación y soporte.'],
                    ]
                ],
                [
                    'level' => 1,
                    'name' => 'Fashion Street',
                    'stores' => [
                        ['name' => 'Aurora', 'desc' => 'Lo último en moda femenina contemporánea.'],
                        ['name' => 'Velvet', 'desc' => 'Calzado premium para toda ocasión.'],
                        ['name' => 'Urban Edge', 'desc' => 'Streetwear y sneakers de edición limitada.'],
                        ['name' => 'Gold & Co.', 'desc' => 'Joyería de lujo y relojería fina.'],
                    ]
                ],
                [
                    'level' => 0,
                    'name' => 'Grand Lobby',
                    'stores' => [
                        ['name' => 'Info Desk', 'desc' => 'Atención al visitante y servicios generales.'],
                        ['name' => 'Fresh Market', 'desc' => 'Productos gourmet y orgánicos seleccionados.'],
                        ['name' => 'Book Haven', 'desc' => 'Librería boutique con títulos exclusivos.'],
                        ['name' => 'Art Gallery', 'desc' => 'Galería de arte rotativa con artistas locales.'],
                    ]
                ]
            ];

            foreach ($mallData as $floorData) {
                $piso = InfraestructurasPisos::create([
                    'infraestructura_id' => $infra->id,
                    'nombre' => $floorData['name'],
                    'cantidad_tiendas' => count($floorData['stores']),
                    'estado' => 'activo',
                ]);

                foreach ($floorData['stores'] as $index => $store) {
                    $tienda = InfraestructurasTiendas::create([
                        'infraestructura_piso_id' => $piso->id,
                        'nombre' => $store['name'],
                        'numero' => sprintf('%03d', ($floorData['level'] * 100) + ($index + 1)),
                        'descripcion' => $store['desc'],
                        'id_estado' => 1,
                        'telefono_referencia' => '+591 7' . rand(1000000, 9999999),
                        'tamano' => rand(20, 100),
                    ]);

                    // Asociar marca a la tienda (muchos a muchos)
                    $tienda->marcas()->attach($marcaGral->id);

                    // Crear productos para la tienda
                    for ($i = 1; $i <= 3; $i++) {
                        $prod = Productos::create([
                            'nombre' => "Producto {$i} de " . $store['name'],
                            'descripcion' => "Descripción detallada del producto {$i} que se vende en " . $store['name'],
                            'precio' => rand(50, 500),
                            'categoria_id' => $catGral->id,
                            'marca_id' => $marcaGral->id,
                            'estado' => 'activo',
                            'infraestructuras_tienda_id' => $tienda->id,
                        ]);

                        // Añadir una imagen de placeholder
                        ProductosImagenes::create([
                            'producto_id' => $prod->id,
                            'url' => 'https://picsum.photos/seed/' . md5($prod->nombre) . '/600/400',
                            'tipo' => 'principal',
                        ]);
                    }
                }
            }
        });
    }
}
