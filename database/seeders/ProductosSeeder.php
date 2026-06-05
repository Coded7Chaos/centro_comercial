<?php

namespace Database\Seeders;

use App\Models\Categorias;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\ProductosImagenes;
use App\Models\Suscripciones;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        // Requiere tiendas y marcas del SuscripcionesSeeder
        $this->call(SuscripcionesSeeder::class);

        // Idempotencia: si TechZone ya tiene productos, este seeder ya corrió
        $marcaId = Marcas::where('nombre', 'TechZone')->value('id');
        if ($marcaId && Productos::where('marca_id', $marcaId)->exists()) {
            $this->command?->info('ProductosSeeder: ya fue ejecutado anteriormente, se omite.');
            return;
        }

        DB::transaction(function () {
            foreach ($this->catalogo() as $item) {
                $marca = Marcas::where('nombre', $item['marca'])->first();
                if (! $marca) {
                    $this->command?->warn("  ✗ Marca '{$item['marca']}' no encontrada.");
                    continue;
                }

                // Buscar tienda a través de la suscripción (más fiable que el pivot
                // marca↔tienda, que el observer desvincula al expirar el contrato)
                $suscripcion = Suscripciones::where('marca_id', $marca->id)->first();
                $tienda = $suscripcion
                    ? InfraestructurasTiendas::find($suscripcion->infraestructuras_tienda_id)
                    : $marca->tiendas()->first();

                if (! $tienda) {
                    $this->command?->warn("  ✗ Sin tienda para la marca '{$item['marca']}'.");
                    continue;
                }

                $categoriaId = Categorias::where('nombre', $item['subcategoria'])->value('id');

                $creados = 0;
                foreach ($item['productos'] as $pd) {
                    $producto = Productos::create([
                        'nombre'                   => $pd['nombre'],
                        'descripcion'              => $pd['descripcion'],
                        'precio'                   => $pd['precio'],
                        'categoria_id'             => $categoriaId,
                        'marca_id'                 => $marca->id,
                        'estado'                   => 'activo',
                        'infraestructuras_tienda_id' => $tienda->id,
                    ]);

                    ProductosImagenes::create([
                        'producto_id' => $producto->id,
                        'url'         => $pd['imagen'],
                        'tipo'        => 'principal',
                    ]);

                    $creados++;
                }

                $this->command?->line("  ✓ {$item['marca']} ({$tienda->nombre}) → {$creados} productos creados");
            }
        });

        $this->command?->info('ProductosSeeder: productos creados exitosamente.');
    }

    private function catalogo(): array
    {
        return [
            // ── TechZone Pro ─────────────────────────────────────────────
            [
                'marca'       => 'TechZone',
                'subcategoria' => 'Accesorios & Gadgets',
                'productos'   => [
                    [
                        'nombre'      => 'Cargador Inalámbrico 15W',
                        'descripcion' => 'Base de carga rápida magnética compatible con iOS y Android.',
                        'precio'      => 120.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1616440347437-b1c73416efc2?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Auriculares Bluetooth Pro',
                        'descripcion' => 'Cancelación activa de ruido y 24 horas de batería.',
                        'precio'      => 380.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1608156639585-b3a032ef9689?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Soporte Magnético Auto',
                        'descripcion' => 'Soporte MagSafe para rejilla de ventilación, instalación en segundos.',
                        'precio'      => 90.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
            ],

            // ── M-Sport ───────────────────────────────────────────────────
            [
                'marca'       => 'M-Sport',
                'subcategoria' => 'Ropa Masculina',
                'productos'   => [
                    [
                        'nombre'      => 'Camiseta Técnica Running',
                        'descripcion' => 'Tela transpirable y de secado rápido para entrenamientos intensos.',
                        'precio'      => 95.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Short Deportivo Pro',
                        'descripcion' => 'Short ligero con bolsillo zip y cintura ajustable.',
                        'precio'      => 75.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Zapatillas Running X1',
                        'descripcion' => 'Amortiguación superior y suela antideslizante para cualquier superficie.',
                        'precio'      => 320.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
            ],

            // ── Sofía's ───────────────────────────────────────────────────
            [
                'marca'       => "Sofía's",
                'subcategoria' => 'Ropa Femenina',
                'productos'   => [
                    [
                        'nombre'      => 'Vestido Lino Verano',
                        'descripcion' => 'Corte recto, disponible en colores neutros y estampados florales.',
                        'precio'      => 210.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Blusa Seda Importada',
                        'descripcion' => 'Seda natural con caída perfecta, ideal para ocasiones formales.',
                        'precio'      => 185.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Pantalón Wide Leg',
                        'descripcion' => 'Corte ancho de tiro alto en tela satinada, muy versátil.',
                        'precio'      => 165.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
            ],

            // ── Gourmet D ─────────────────────────────────────────────────
            [
                'marca'       => 'Gourmet D',
                'subcategoria' => 'Gourmet & Delicatessen',
                'productos'   => [
                    [
                        'nombre'      => 'Tabla de Quesos Selectos',
                        'descripcion' => 'Surtido de quesos curados europeos con frutas y nueces.',
                        'precio'      => 120.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Vino Malbec Reserva 750ml',
                        'descripcion' => 'Vino tinto de autor con notas de mora y vainilla, cosecha 2021.',
                        'precio'      => 145.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1502164980785-f8aa41d53611?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Pack Embutidos Ibéricos',
                        'descripcion' => 'Selección de jamón serrano, chorizo y salchichón importado.',
                        'precio'      => 98.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
            ],

            // ── Cami ─────────────────────────────────────────────────────
            [
                'marca'       => 'Cami',
                'subcategoria' => 'Café & Pastelería',
                'productos'   => [
                    [
                        'nombre'      => 'Café Espresso Doble',
                        'descripcion' => 'Extracción intensa de granos 100% Arábica de origen único.',
                        'precio'      => 18.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Cappuccino Vainilla',
                        'descripcion' => 'Espresso con leche vaporizada sedosa y un toque de vainilla.',
                        'precio'      => 22.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Tarta de Chocolate',
                        'descripcion' => 'Tarta húmeda con ganache de cacao al 70%, sin gluten.',
                        'precio'      => 35.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
            ],

            // ── Arte Flores ───────────────────────────────────────────────
            [
                'marca'       => 'Arte Flores',
                'subcategoria' => 'Galería de Arte',
                'productos'   => [
                    [
                        'nombre'      => 'Cuadro Acrílico Andino',
                        'descripcion' => 'Pintura original 40×60 cm con técnica mixta y motivos bolivianos.',
                        'precio'      => 350.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Escultura de Cerámica',
                        'descripcion' => 'Pieza artesanal a mano, diseño contemporáneo de 20 cm de altura.',
                        'precio'      => 180.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1531913764164-f85c52e6e654?w=600&auto=format&fit=crop&q=80',
                    ],
                    [
                        'nombre'      => 'Tejido Aguayo Decorativo',
                        'descripcion' => 'Textil tradicional boliviano 50×80 cm, ideal para decoración.',
                        'precio'      => 95.00,
                        'imagen'      => 'https://images.unsplash.com/photo-1548438294-1ad5d5f4f063?w=600&auto=format&fit=crop&q=80',
                    ],
                ],
            ],
        ];
    }
}
