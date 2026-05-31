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

class ClientCustomizationTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $client;
    protected $shop;
    protected $brand;
    protected $category;
    protected $subcat;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create client user and relation
        $this->user = User::factory()->create();
        $this->user->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $this->user->id,
            'ci' => '9999999',
            'numero_celular' => '79999999',
            'genero' => 'femenino',
        ]);

        // Get or create piso & infra
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

        // Create shop for client
        $this->shop = InfraestructurasTiendas::create([
            'numero' => 701,
            'tamano' => '15x15',
            'id_estado' => 1,
            'cliente_id' => $this->client->id,
            'infraestructura_piso_id' => $piso->id,
            'nombre' => 'Original Shop Name',
            'descripcion' => 'Original Shop Desc',
        ]);

        // Create a brand
        $this->brand = Marcas::create([
            'nombre' => 'Custom Brand',
            'cliente_id' => $this->client->id,
            'estado' => 'activo',
        ]);

        // Create category & subcategory
        $this->category = Categorias::create([
            'nombre' => 'Moda',
            'tipo' => 'categoria',
        ]);
        $this->subcat = Categorias::create([
            'nombre' => 'Zapatos',
            'tipo' => 'subcategoria',
            'categoria_padre_id' => $this->category->id,
        ]);
    }

    /**
     * Test customization page access.
     */
    public function test_client_can_access_personalization_page(): void
    {
        $response = $this->actingAs($this->user)->get('/cliente/personalizar');
        $response->assertStatus(200);
        $response->assertViewIs('cliente.personalizar');
    }

    /**
     * Test updating the shop's profile details.
     */
    public function test_client_can_update_shop_profile(): void
    {
        $foto = UploadedFile::fake()->image('tienda.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/tienda', [
            'tienda_id' => $this->shop->id,
            'nombre' => 'Shop Profile Name',
            'descripcion' => 'Shop Profile Desc',
            'telefono_referencia' => '71112222',
            'foto' => $foto,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->shop->refresh();
        $this->assertEquals('Shop Profile Name', $this->shop->nombre);
        $this->assertEquals('Shop Profile Desc', $this->shop->descripcion);
        $this->assertEquals('71112222', $this->shop->telefono_referencia);
        $this->assertNotNull($this->shop->foto_referencial);
        Storage::disk('public')->assertExists($this->shop->foto_referencial);
    }

    /**
     * Test updating shop personalization details.
     */
    public function test_client_can_update_shop_personalization(): void
    {
        $response = $this->actingAs($this->user)->post('/cliente/personalizar/tienda', [
            'tienda_id' => $this->shop->id,
            'nombre' => 'Shop Personalizado Name',
            'descripcion' => 'Shop Personalizado Desc',
            'telefono_referencia' => '73334444',
            'marca_id' => $this->brand->id,
        ]);

        $response->assertRedirect(route('cliente.personalizar', ['tienda_id' => $this->shop->id]));
        $response->assertSessionHas('success');

        $this->shop->refresh();
        $this->assertEquals('Shop Personalizado Name', $this->shop->nombre);
        $this->assertEquals('Shop Personalizado Desc', $this->shop->descripcion);
        $this->assertEquals('73334444', $this->shop->telefono_referencia);
        
        $this->assertTrue($this->shop->marcas->contains($this->brand->id));
    }

    /**
     * Test updating an existing product in the showcase/vitrina.
     */
    public function test_client_can_update_showcase_product(): void
    {
        $product = Productos::create([
            'nombre' => 'Old Product',
            'precio' => 100,
            'descripcion' => 'Old Desc',
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $this->subcat->id,
            'marca_id' => $this->brand->id,
            'estado' => 'activo',
        ]);

        $foto = UploadedFile::fake()->image('showcase_updated.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/personalizar/producto', [
            'producto_id' => $product->id,
            'tienda_id' => $this->shop->id,
            'nombre' => 'Updated Showcase Product',
            'precio' => 120,
            'descripcion' => 'Updated Showcase Desc',
            'imagen' => $foto,
        ]);

        $response->assertRedirect(route('cliente.personalizar', ['tienda_id' => $this->shop->id]));
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('Updated Showcase Product', $product->nombre);
        $this->assertEquals(120, $product->precio);
        $this->assertEquals('Updated Showcase Desc', $product->descripcion);
        
        $img = $product->imagenes()->where('tipo', 'principal')->first();
        $this->assertNotNull($img);
        Storage::disk('public')->assertExists($img->url);
    }

    /**
     * Test creating a brand new showcase product.
     */
    public function test_client_can_create_new_showcase_product(): void
    {
        $foto = UploadedFile::fake()->image('new_showcase.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/personalizar/producto', [
            'tienda_id' => $this->shop->id,
            'nombre' => 'Brand New Showcase Product',
            'precio' => 150,
            'descripcion' => 'Brand New Showcase Desc',
            'categoria_id' => $this->category->id,
            'subcategoria_id' => $this->subcat->id,
            'marca_id' => $this->brand->id,
            'imagen' => $foto,
        ]);

        $response->assertRedirect(route('cliente.personalizar', ['tienda_id' => $this->shop->id]));
        $response->assertSessionHas('success');

        $product = Productos::where('nombre', 'Brand New Showcase Product')->first();
        $this->assertNotNull($product);
        $this->assertEquals(150, $product->precio);
        $this->assertEquals('Brand New Showcase Desc', $product->descripcion);
        $this->assertEquals($this->shop->id, $product->infraestructuras_tienda_id);
        $this->assertEquals($this->subcat->id, $product->categoria_id);
        $this->assertEquals($this->brand->id, $product->marca_id);

        $img = $product->imagenes()->where('tipo', 'principal')->first();
        $this->assertNotNull($img);
        Storage::disk('public')->assertExists($img->url);
    }

    /**
     * Test updating a vitrina/escaparate slot image.
     */
    public function test_client_can_update_vitrina_image(): void
    {
        $foto = UploadedFile::fake()->image('showcase_slot.jpg');

        $response = $this->actingAs($this->user)->post('/cliente/personalizar/vitrina', [
            'tienda_id' => $this->shop->id,
            'slot' => 1,
            'imagen' => $foto,
        ]);

        $response->assertRedirect(route('cliente.personalizar', ['tienda_id' => $this->shop->id]));
        $response->assertSessionHas('success');

        $this->shop->refresh();
        $this->assertNotNull($this->shop->vitrina_1);
        Storage::disk('public')->assertExists($this->shop->vitrina_1);
    }
}
