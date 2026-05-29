<?php

namespace Tests\Feature;

use App\Models\Categorias;
use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientProductsCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $client;
    protected $shop;
    protected $category;
    protected $subcategory;
    protected $brand;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create client user
        $this->user = User::factory()->create();
        $this->user->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $this->user->id,
            'ci' => '2222222',
            'numero_celular' => '71111111',
            'genero' => 'masculino',
        ]);

        $piso = \App\Models\InfraestructurasPisos::first();
        if (!$piso) {
            $infra = \App\Models\Infraestructuras::first() ?? \App\Models\Infraestructuras::create([
                'nombre' => 'Mall Gran Vía',
                'ubicacion' => 'La Paz',
            ]);
            $piso = \App\Models\InfraestructurasPisos::create([
                'nombre' => 'Piso 1',
                'infraestructura_id' => $infra->id,
            ]);
        }
        $this->piso = $piso;

        // Create a shop assigned to this client
        $this->shop = InfraestructurasTiendas::create([
            'numero' => 777,
            'tamano' => '15x15',
            'id_estado' => 1,
            'cliente_id' => $this->client->id,
            'nombre' => 'Mi Tienda Test',
            'infraestructura_piso_id' => $piso->id,
        ]);

        // Create category and subcategory
        $this->category = Categorias::create([
            'nombre' => 'Moda',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);
        $this->subcategory = Categorias::create([
            'nombre' => 'Calzado',
            'tipo' => 'subcategoria',
            'categoria_padre_id' => $this->category->id,
            'estado' => 'activo',
        ]);

        // Create brand
        $this->brand = Marcas::create([
            'nombre' => 'Nike Test',
            'estado' => 'activo',
            'cliente_id' => $this->client->id,
        ]);
    }

    public function test_create_product_form_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/cliente/productos/crear');
        $response->assertStatus(200);
    }

    public function test_store_product_success(): void
    {
        $file = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/productos', [
            'nombre' => 'Nike Air Max',
            'precio' => 750.50,
            'descripcion' => 'Zapatillas deportivas',
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $this->category->id,
            'subcategoria_id' => $this->subcategory->id,
            'marca_id' => $this->brand->id,
            'imagen' => $file,
        ]);

        $response->assertRedirect(route('cliente.productos.index'));
        
        $this->assertDatabaseHas('productos', [
            'nombre' => 'Nike Air Max',
            'precio' => 750.50,
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $this->subcategory->id,
            'marca_id' => $this->brand->id,
        ]);
    }

    public function test_store_product_validation_failure(): void
    {
        $response = $this->actingAs($this->user)->post('/cliente/productos', [
            // Missing all required fields
        ]);

        $response->assertSessionHasErrors(['nombre', 'precio', 'infraestructuras_tienda_id', 'categoria_id', 'subcategoria_id', 'marca_id', 'imagen']);
    }

    public function test_edit_product_form_loads_for_own_product(): void
    {
        $product = Productos::create([
            'nombre' => 'Adidas Boost',
            'precio' => 600,
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $this->subcategory->id,
            'marca_id' => $this->brand->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->get("/cliente/productos/{$product->id}/editar");
        $response->assertStatus(200);
    }

    public function test_edit_product_form_denied_for_other_client_product(): void
    {
        // Create another client & shop & product
        $otherUser = User::factory()->create();
        $otherUser->assignRole('cliente');
        $otherClient = Clientes::create([
            'user_id' => $otherUser->id,
            'ci' => '3333333',
            'numero_celular' => '72222222',
            'genero' => 'femenino',
        ]);
        $otherShop = InfraestructurasTiendas::create([
            'numero' => 888,
            'tamano' => '10x10',
            'id_estado' => 1,
            'cliente_id' => $otherClient->id,
            'infraestructura_piso_id' => $this->piso->id,
        ]);
        $otherProduct = Productos::create([
            'nombre' => 'Puma Suede',
            'precio' => 450,
            'infraestructuras_tienda_id' => $otherShop->id,
            'categoria_id' => $this->subcategory->id,
            'marca_id' => $this->brand->id,
            'estado' => 'activo',
        ]);

        // Attempting to edit other client's product should abort (404)
        $response = $this->actingAs($this->user)->get("/cliente/productos/{$otherProduct->id}/editar");
        $response->assertStatus(404);
    }

    public function test_update_product_success(): void
    {
        $product = Productos::create([
            'nombre' => 'Adidas Boost',
            'precio' => 600,
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $this->subcategory->id,
            'marca_id' => $this->brand->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->put("/cliente/productos/{$product->id}", [
            'nombre' => 'Adidas Boost V2',
            'precio' => 650,
            'descripcion' => 'Zapatos deportivos actualizados',
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $this->category->id,
            'subcategoria_id' => $this->subcategory->id,
            'marca_id' => $this->brand->id,
        ]);

        $response->assertRedirect(route('cliente.productos.index'));

        $this->assertDatabaseHas('productos', [
            'id' => $product->id,
            'nombre' => 'Adidas Boost V2',
            'precio' => 650,
        ]);
    }

    public function test_delete_product_success(): void
    {
        $product = Productos::create([
            'nombre' => 'Adidas Boost',
            'precio' => 600,
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $this->subcategory->id,
            'marca_id' => $this->brand->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->delete("/cliente/productos/{$product->id}");
        $response->assertRedirect(route('cliente.productos.index'));

        $this->assertDatabaseMissing('productos', [
            'id' => $product->id,
        ]);
    }
}
