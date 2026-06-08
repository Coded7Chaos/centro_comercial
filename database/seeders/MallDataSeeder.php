<?php

namespace Database\Seeders;

use App\Models\Categorias;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Clientes;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class MallDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Crear Estructura Jerárquica de Categorías Reales
            $categoriasEstructura = [
                'Gastronomía' => [
                    'Café & Pastelería',
                    'Comida Asiática',
                    'Bares & Coctelería',
                    'Gourmet & Delicatessen'
                ],
                'Tecnología' => [
                    'Smartphones',
                    'Accesorios & Gadgets',
                    'Robótica & Drones',
                    'Videojuegos & Consolas'
                ],
                'Moda & Estilo' => [
                    'Ropa Femenina',
                    'Ropa Masculina',
                    'Calzado Premium',
                    'Joyería & Relojería'
                ],
                'Entretenimiento & Cultura' => [
                    'Librería & Cómics',
                    'Galería de Arte',
                    'Juegos de Mesa'
                ]
            ];

            foreach ($categoriasEstructura as $padreNombre => $subcategorias) {
                $padre = Categorias::firstOrCreate(['nombre' => $padreNombre], [
                    'descripcion' => "Categoría principal de {$padreNombre}",
                    'estado' => 'activo',
                    'tipo' => 'categoria',
                ]);

                foreach ($subcategorias as $subNombre) {
                    Categorias::firstOrCreate([
                        'nombre' => $subNombre,
                        'categoria_padre_id' => $padre->id
                    ], [
                        'descripcion' => "Subcategoría de {$subNombre}",
                        'estado' => 'activo',
                        'tipo' => 'categoria',
                    ]);
                }
            }

            // Fallback general
            Categorias::firstOrCreate(['nombre' => 'General'], [
                'descripcion' => 'Categoría general por defecto',
                'estado' => 'activo',
                'tipo' => 'categoria',
            ]);

            // 2. Crear Infraestructura (Marble Galleria)
            $admin = User::role(['super_admin', 'admin'])->with('cliente')->orderBy('id')->first();
            $adminPhone = $admin?->cliente?->numero_celular ?? '+591 7000 0000';
            $adminEmail = $admin?->email ?? 'admin@admin.com';
            $openingDate = Carbon::parse('2024-01-01 09:00:00');

            $infra = Infraestructuras::firstOrCreate(
                ['nombre' => 'Marble Galleria'],
                [
                    'ubicacion' => 'Distrito Central, Av. Principal #123',
                    'lat'       => '-16.5000',
                    'long'      => '-68.1500',
                    'pisos'     => 4,
                    'created_at' => $openingDate,
                    'updated_at' => $openingDate,
                ]
            );

            if (! $infra->created_at || $infra->created_at->gt($openingDate)) {
                DB::table('infraestructuras')
                    ->where('id', $infra->id)
                    ->update(['created_at' => $openingDate]);
                $infra->created_at = $openingDate;
            }

            // Si esta infraestructura ya tiene pisos cargados, asumimos que el seed ya corrió.
            if ($infra->pisosInfraestructura()->exists()) {
                DB::table('infraestructuras_pisos')
                    ->where('infraestructura_id', $infra->id)
                    ->where('created_at', '>', $openingDate)
                    ->update(['created_at' => $openingDate]);

                DB::table('infraestructuras_tiendas')
                    ->whereIn(
                        'infraestructura_piso_id',
                        $infra->pisosInfraestructura()->pluck('id')
                    )
                    ->where('created_at', '>', $openingDate)
                    ->update(['created_at' => $openingDate]);

                InfraestructurasTiendas::whereHas('piso', fn ($query) => $query->where('infraestructura_id', $infra->id))
                    ->whereNull('cliente_id')
                    ->where(function ($query) {
                        $query->whereNull('telefono_referencia')
                            ->orWhereNull('email_contacto');
                    })
                    ->update([
                        'telefono_referencia' => $adminPhone,
                        'email_contacto' => $adminEmail,
                    ]);

                return;
            }

            // 3. Estructura de Tiendas vacías por piso
            $floors = [
                ['level' => 3, 'name' => 'Sky Lounge', 'imagen_fondo' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=2000&q=80', 'count' => 4, 'numero' => 'Piso 3'],
                ['level' => 2, 'name' => 'Tech Plaza', 'imagen_fondo' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&w=2000&q=80', 'count' => 4, 'numero' => 'Piso 2'],
                ['level' => 1, 'name' => 'Fashion Street', 'imagen_fondo' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=2000&q=80', 'count' => 4, 'numero' => 'Piso 1'],
                ['level' => 0, 'name' => 'Grand Lobby', 'imagen_fondo' => 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=2000&q=80', 'count' => 4, 'numero' => 'Planta baja'],
            ];

            foreach ($floors as $floorData) {
                $piso = InfraestructurasPisos::create([
                    'infraestructura_id' => $infra->id,
                    'nombre' => $floorData['name'],
                    'numero' => $floorData['numero'],
                    'cantidad_tiendas' => $floorData['count'],
                    'estado' => 'activo',
                    'imagen_fondo' => $floorData['imagen_fondo'],
                    'created_at' => $openingDate,
                    'updated_at' => $openingDate,
                ]);
                DB::table('infraestructuras_pisos')
                    ->where('id', $piso->id)
                    ->update([
                        'created_at' => $openingDate,
                        'updated_at' => $openingDate,
                    ]);

                for ($index = 0; $index < $floorData['count']; $index++) {
                    $tienda = InfraestructurasTiendas::create([
                        'infraestructura_piso_id' => $piso->id,
                        'nombre' => null, // Disponible/Vacía no tiene nombre comercial
                        'numero' => sprintf('%03d', ($floorData['level'] * 100) + ($index + 1)),
                        'descripcion' => null,
                        'id_estado' => 1, // Disponible
                        'telefono_referencia' => $adminPhone,
                        'email_contacto' => $adminEmail,
                        'tamano' => rand(25, 90),
                        'cliente_id' => null,
                        'created_at' => $openingDate,
                        'updated_at' => $openingDate,
                    ]);
                    DB::table('infraestructuras_tiendas')
                        ->where('id', $tienda->id)
                        ->update([
                            'created_at' => $openingDate,
                            'updated_at' => $openingDate,
                        ]);
                }
            }
        });
    }
}
