<?php

namespace Tests\Feature;

use App\Filament\Resources\Productos\Pages\EditProductos;
use App\Filament\Resources\Productos\Pages\ListProductos;
use App\Filament\Resources\Productos\ProductosResource;
use App\Models\Categorias;
use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\ProductosImagenes;
use App\Models\User;
use App\Support\ActiveInfraestructura;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProductEditFormTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_product_edit_form_prefills_all_relationship_fields(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');

        $cliente = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '9988776',
            'numero_celular' => '70000001',
            'genero' => 'femenino',
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Producto Admin',
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
            'id_estado' => EstadoTienda::firstOrCreate(['estado' => 'Alquilada'])->id,
            'numero' => 'A-101',
            'nombre' => 'Cafetería Test',
            'tamano' => '25',
        ]);

        $categoria = Categorias::create([
            'nombre' => 'Gastronomía',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);

        $subcategoria = Categorias::create([
            'nombre' => 'Café',
            'tipo' => 'subcategoria',
            'categoria_padre_id' => $categoria->id,
            'estado' => 'activo',
        ]);

        $marca = Marcas::create([
            'nombre' => 'Café Test',
            'estado' => 'activo',
            'cliente_id' => $cliente->id,
        ]);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('cliente');

        $otherCliente = Clientes::create([
            'user_id' => $otherUser->id,
            'ci' => '8877665',
            'numero_celular' => '70000002',
            'genero' => 'masculino',
        ]);

        $otherTienda = InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'cliente_id' => $otherCliente->id,
            'id_estado' => EstadoTienda::firstOrCreate(['estado' => 'Alquilada'])->id,
            'numero' => 'B-202',
            'nombre' => 'Tienda Otro Cliente',
            'tamano' => '30',
        ]);

        InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'cliente_id' => null,
            'id_estado' => EstadoTienda::firstOrCreate(['estado' => 'Disponible'])->id,
            'numero' => 'D-303',
            'nombre' => 'Tienda Disponible Test',
            'tamano' => '35',
        ]);

        $otherMarca = Marcas::create([
            'nombre' => 'Marca Privada Otro Cliente',
            'estado' => 'activo',
            'cliente_id' => $otherCliente->id,
        ]);

        $producto = Productos::create([
            'nombre' => 'Café Espresso doble',
            'precio' => 18,
            'descripcion' => 'Extracción intensa de granos 100% Arábica de origen único.',
            'infraestructuras_tienda_id' => $tienda->id,
            'categoria_id' => $subcategoria->id,
            'marca_id' => $marca->id,
            'estado' => 'activo',
        ]);

        ProductosImagenes::create([
            'producto_id' => $producto->id,
            'url' => 'productos/cafe.jpg',
            'tipo' => 'principal',
        ]);

        ActiveInfraestructura::setId($infraestructura->id);

        Livewire::actingAs($superAdmin)
            ->test(EditProductos::class, ['record' => $producto->getRouteKey()])
            ->assertSet('data.cliente_temp', $cliente->id)
            ->assertSet('data.infraestructuras_tienda_id', $tienda->id)
            ->assertSet('data.categoria_id', $categoria->id)
            ->assertSet('data.subcategoria_id', $subcategoria->id)
            ->assertSet('data.marca_id', $marca->id)
            ->assertSet('data.imagenes', fn ($imagenes) => collect($imagenes)
                ->contains(fn ($imagen) => collect($imagen['url'] ?? [])->contains('productos/cafe.jpg')
                )
            )
            ->assertSee('Tienda Otro Cliente')
            ->assertDontSee('Tienda Disponible Test')
            ->set('data.infraestructuras_tienda_id', $otherTienda->id)
            ->assertSet('data.cliente_temp', $otherCliente->id)
            ->set('data.marca_id', $otherMarca->id)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(ProductosResource::getUrl('index'));

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'infraestructuras_tienda_id' => $otherTienda->id,
            'categoria_id' => $subcategoria->id,
            'marca_id' => $otherMarca->id,
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('productos_imagenes', [
            'producto_id' => $producto->id,
            'url' => 'productos/cafe.jpg',
            'tipo' => 'principal',
        ]);
    }

    public function test_admin_products_table_shows_store_name_and_hides_status_column(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');

        $cliente = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '7766554',
            'numero_celular' => '70000003',
            'genero' => 'femenino',
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Tabla Producto',
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
            'id_estado' => EstadoTienda::firstOrCreate(['estado' => 'Alquilada'])->id,
            'numero' => 'T-404',
            'nombre' => 'Cafetería Tabla Test',
            'tamano' => '25',
        ]);

        $categoria = Categorias::create([
            'nombre' => 'Bebidas',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);

        $marca = Marcas::create([
            'nombre' => 'Marca Tabla Test',
            'estado' => 'activo',
            'cliente_id' => $cliente->id,
        ]);

        Productos::create([
            'nombre' => 'Latte de prueba',
            'precio' => 22,
            'descripcion' => 'Producto para tabla',
            'infraestructuras_tienda_id' => $tienda->id,
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'estado' => 'activo',
        ]);

        ActiveInfraestructura::setId($infraestructura->id);

        Livewire::actingAs($admin)
            ->test(ListProductos::class)
            ->assertSee('Cafetería Tabla Test')
            ->assertDontSee('Estado');
    }

    public function test_super_admin_can_delete_product_from_table_with_comment_and_sends_notification(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');

        $cliente = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '123456789',
            'numero_celular' => '70000005',
            'genero' => 'masculino',
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Deletion Test',
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
            'id_estado' => EstadoTienda::firstOrCreate(['estado' => 'Alquilada'])->id,
            'numero' => 'T-505',
            'nombre' => 'Café Deleteme',
            'tamano' => '20',
        ]);

        $categoria = Categorias::create([
            'nombre' => 'Alimentos',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);

        $marca = Marcas::create([
            'nombre' => 'Marca Deleteme',
            'estado' => 'activo',
            'cliente_id' => $cliente->id,
        ]);

        $producto = Productos::create([
            'nombre' => 'Galleta de Avena',
            'precio' => 10,
            'descripcion' => 'Galleta saludable',
            'infraestructuras_tienda_id' => $tienda->id,
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'estado' => 'activo',
        ]);

        ProductosImagenes::create([
            'producto_id' => $producto->id,
            'url' => 'productos/galleta.jpg',
            'tipo' => 'principal',
        ]);

        ActiveInfraestructura::setId($infraestructura->id);

        Livewire::actingAs($superAdmin)
            ->test(ListProductos::class)
            ->callTableAction('delete', $producto, [
                'comentario' => 'Este producto ya no se vende.'
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);

        $this->assertDatabaseHas('client_notifications', [
            'cliente_id' => $cliente->id,
            'titulo' => 'Un administrador ha eliminado tu producto',
            'mensaje' => 'Tu producto "Galleta de Avena" ha sido eliminado por un administrador. Razón: Este producto ya no se vende.',
        ]);
    }

    public function test_super_admin_can_delete_product_from_edit_page_with_comment_and_sends_notification(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');

        $cliente = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '987654321',
            'numero_celular' => '70000006',
            'genero' => 'femenino',
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Deletion Page Test',
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
            'id_estado' => EstadoTienda::firstOrCreate(['estado' => 'Alquilada'])->id,
            'numero' => 'T-606',
            'nombre' => 'Café Deleteme Edit',
            'tamano' => '20',
        ]);

        $categoria = Categorias::create([
            'nombre' => 'Alimentos',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);

        $marca = Marcas::create([
            'nombre' => 'Marca Deleteme Edit',
            'estado' => 'activo',
            'cliente_id' => $cliente->id,
        ]);

        $producto = Productos::create([
            'nombre' => 'Muffin de Chocolate',
            'precio' => 15,
            'descripcion' => 'Muffin dulce',
            'infraestructuras_tienda_id' => $tienda->id,
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'estado' => 'activo',
        ]);

        ProductosImagenes::create([
            'producto_id' => $producto->id,
            'url' => 'productos/muffin.jpg',
            'tipo' => 'principal',
        ]);

        ActiveInfraestructura::setId($infraestructura->id);

        Livewire::actingAs($superAdmin)
            ->test(EditProductos::class, ['record' => $producto->getRouteKey()])
            ->callAction('delete', [
                'comentario' => 'Producto agotado.'
            ])
            ->assertHasNoActionErrors()
            ->assertRedirect(ProductosResource::getUrl('index'));

        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);

        $this->assertDatabaseHas('client_notifications', [
            'cliente_id' => $cliente->id,
            'titulo' => 'Un administrador ha eliminado tu producto',
            'mensaje' => 'Tu producto "Muffin de Chocolate" ha sido eliminado por un administrador. Razón: Producto agotado.',
        ]);
    }
}
