<?php

namespace Tests\Feature;

use App\Models\Categorias;
use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\ProductosImagenes;
use App\Models\Suscripciones;
use App\Models\User;
use App\Services\ExpiredSubscriptionsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExpiredSubscriptionsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_expired_contract_releases_store_and_blocks_client_without_future_contracts(): void
    {
        [$cliente, $tienda] = $this->createClientStore();

        $categoria = Categorias::create([
            'nombre' => 'Privada Test',
            'descripcion' => 'Categoría privada',
            'estado' => 'activo',
            'tipo' => 'categoria',
            'cliente_id' => $cliente->id,
        ]);

        $subcategoria = Categorias::create([
            'nombre' => 'Sub Privada Test',
            'descripcion' => 'Subcategoría privada',
            'estado' => 'activo',
            'tipo' => 'subcategoria',
            'categoria_padre_id' => $categoria->id,
            'cliente_id' => $cliente->id,
        ]);

        $marca = Marcas::create([
            'nombre' => 'Marca Privada Test',
            'descripcion' => 'Marca del cliente',
            'cliente_id' => $cliente->id,
            'estado' => 'activo',
        ]);

        $tienda->marcas()->sync([$marca->id]);

        $producto = Productos::create([
            'nombre' => 'Producto Test',
            'descripcion' => 'Producto del contrato vencido',
            'precio' => 100,
            'categoria_id' => $subcategoria->id,
            'marca_id' => $marca->id,
            'estado' => 'activo',
            'infraestructuras_tienda_id' => $tienda->id,
        ]);

        ProductosImagenes::create([
            'producto_id' => $producto->id,
            'url' => 'productos/test.jpg',
            'tipo' => 'principal',
        ]);

        Suscripciones::withoutEvents(fn () => Suscripciones::create([
            'cliente_id' => $cliente->id,
            'infraestructuras_tienda_id' => $tienda->id,
            'infraestructuras_piso_id' => $tienda->infraestructura_piso_id,
            'fecha_inicio' => '2026-05-01',
            'fecha_fin' => '2026-05-31',
            'tipo' => '1 mes',
            'precio' => 1000,
        ]));

        $released = app(ExpiredSubscriptionsService::class)->process('2026-06-07');

        $this->assertGreaterThanOrEqual(1, $released);

        $tienda->refresh();
        $this->assertNull($tienda->cliente_id);
        $this->assertNull($tienda->nombre);
        $this->assertNull($tienda->descripcion);
        $this->assertNull($tienda->telefono_referencia);
        $this->assertNull($tienda->vitrina_1);
        $this->assertEquals('Disponible', $tienda->estado?->estado);
        $this->assertCount(0, $tienda->marcas()->get());

        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
        $this->assertSoftDeleted('marcas', ['id' => $marca->id]);
        $this->assertDatabaseMissing('categorias', ['id' => $subcategoria->id]);
        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
        $this->assertSoftDeleted('users', ['id' => $cliente->user_id]);
    }

    public function test_expired_contract_does_not_block_client_with_current_or_future_contract(): void
    {
        [$cliente, $expiredStore] = $this->createClientStore();
        [, $futureStore] = $this->createClientStore($cliente);

        Suscripciones::withoutEvents(fn () => Suscripciones::create([
            'cliente_id' => $cliente->id,
            'infraestructuras_tienda_id' => $expiredStore->id,
            'infraestructuras_piso_id' => $expiredStore->infraestructura_piso_id,
            'fecha_inicio' => '2026-05-01',
            'fecha_fin' => '2026-05-31',
            'tipo' => '1 mes',
            'precio' => 1000,
        ]));

        Suscripciones::withoutEvents(fn () => Suscripciones::create([
            'cliente_id' => $cliente->id,
            'infraestructuras_tienda_id' => $futureStore->id,
            'infraestructuras_piso_id' => $futureStore->infraestructura_piso_id,
            'fecha_inicio' => '2026-06-10',
            'fecha_fin' => '2026-07-09',
            'tipo' => '1 mes',
            'precio' => 1000,
        ]));

        app(ExpiredSubscriptionsService::class)->process('2026-06-07');

        $this->assertNotSoftDeleted('clientes', ['id' => $cliente->id]);
        $this->assertNotSoftDeleted('users', ['id' => $cliente->user_id]);

        app(ExpiredSubscriptionsService::class)->process('2026-06-10');

        $futureStore->refresh();
        $this->assertSame($cliente->id, $futureStore->cliente_id);
        $this->assertEquals('Alquilada', $futureStore->estado?->estado);
    }

    private function createClientStore(?Clientes $cliente = null): array
    {
        EstadoTienda::firstOrCreate(['estado' => 'Disponible']);
        $estadoAlquilada = EstadoTienda::firstOrCreate(['estado' => 'Alquilada']);

        if (! $cliente) {
            $user = User::factory()->create();

            $cliente = Clientes::create([
                'user_id' => $user->id,
                'ci' => (string) random_int(1000000, 9999999),
                'numero_celular' => '70000000',
                'genero' => 'femenino',
            ]);
        }

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Vencimiento Test '.uniqid(),
            'pisos' => 1,
            'ubicacion' => 'La Paz',
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infraestructura->id,
            'nombre' => 'Piso 1',
        ]);

        $tienda = InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'cliente_id' => $cliente->id,
            'id_estado' => $estadoAlquilada->id,
            'numero' => (string) random_int(100, 999),
            'nombre' => 'Nombre Comercial Cliente',
            'descripcion' => 'Descripción puesta por el cliente',
            'telefono_referencia' => '+591 70000000',
            'tamano' => '30',
            'vitrina_1' => 'vitrinas/test-1.jpg',
            'vitrina_2' => 'vitrinas/test-2.jpg',
            'vitrina_3' => 'vitrinas/test-3.jpg',
        ]);

        return [$cliente, $tienda];
    }
}
