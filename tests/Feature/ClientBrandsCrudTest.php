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

class ClientBrandsCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $client;
    protected $shop;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create client user
        $this->user = User::factory()->create();
        $this->user->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $this->user->id,
            'ci' => '4444444',
            'numero_celular' => '74444444',
            'genero' => 'masculino',
        ]);

        // Get or create piso
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
            'numero' => 601,
            'tamano' => '12x12',
            'id_estado' => 1,
            'cliente_id' => $this->client->id,
            'infraestructura_piso_id' => $piso->id,
        ]);
    }

    public function test_create_brand_form_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/cliente/marcas/crear');
        $response->assertStatus(200);
    }

    public function test_store_brand_success(): void
    {
        $logo = UploadedFile::fake()->image('logo.png');

        $response = $this->actingAs($this->user)->post('/cliente/marcas', [
            'nombre' => 'Mi Marca Privada',
            'descripcion' => 'Descripción de mi marca privada',
            'logo' => $logo,
        ]);

        $response->assertRedirect(route('cliente.marcas.index'));

        $this->assertDatabaseHas('marcas', [
            'nombre' => 'Mi Marca Privada',
            'descripcion' => 'Descripción de mi marca privada',
            'cliente_id' => $this->client->id,
        ]);
    }

    public function test_edit_brand_form_loads_for_own_brand(): void
    {
        $brand = Marcas::create([
            'nombre' => 'Mi Marca Propia',
            'descripcion' => 'Propia',
            'cliente_id' => $this->client->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->get("/cliente/marcas/{$brand->id}/editar");
        $response->assertStatus(200);
    }

    public function test_edit_brand_form_denied_for_other_client_brand(): void
    {
        // Create another client & brand
        $otherUser = User::factory()->create();
        $otherUser->assignRole('cliente');
        $otherClient = Clientes::create([
            'user_id' => $otherUser->id,
            'ci' => '5555555',
            'numero_celular' => '75555555',
            'genero' => 'femenino',
        ]);

        $otherBrand = Marcas::create([
            'nombre' => 'Marca Ajena',
            'descripcion' => 'Ajena',
            'cliente_id' => $otherClient->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->get("/cliente/marcas/{$otherBrand->id}/editar");
        $response->assertStatus(404);
    }

    public function test_update_brand_success(): void
    {
        $brand = Marcas::create([
            'nombre' => 'Nike Fake',
            'descripcion' => 'Original',
            'cliente_id' => $this->client->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->put("/cliente/marcas/{$brand->id}", [
            'nombre' => 'Nike Fake Actualizada',
            'descripcion' => 'Modificada',
        ]);

        $response->assertRedirect(route('cliente.marcas.index'));

        $this->assertDatabaseHas('marcas', [
            'id' => $brand->id,
            'nombre' => 'Nike Fake Actualizada',
            'descripcion' => 'Modificada',
        ]);
    }

    public function test_delete_brand_success_when_no_dependencies(): void
    {
        $brand = Marcas::create([
            'nombre' => 'Eliminame',
            'cliente_id' => $this->client->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->delete("/cliente/marcas/{$brand->id}");
        $response->assertRedirect(route('cliente.marcas.index'));

        $this->assertSoftDeleted('marcas', [
            'id' => $brand->id,
        ]);
    }

    public function test_delete_brand_blocked_when_assigned_to_shop(): void
    {
        $brand = Marcas::create([
            'nombre' => 'Marca En Tienda',
            'cliente_id' => $this->client->id,
            'estado' => 'activo',
        ]);

        // Associate brand with shop
        $this->shop->marcas()->sync([$brand->id]);

        $response = $this->actingAs($this->user)->delete("/cliente/marcas/{$brand->id}");
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('marcas', [
            'id' => $brand->id,
        ]);
    }

    public function test_delete_brand_blocked_when_assigned_to_product(): void
    {
        $brand = Marcas::create([
            'nombre' => 'Marca En Producto',
            'cliente_id' => $this->client->id,
            'estado' => 'activo',
        ]);

        // Create product with this brand
        $category = Categorias::create([
            'nombre' => 'Comida',
            'tipo' => 'categoria',
        ]);
        $subcat = Categorias::create([
            'nombre' => 'Bebidas',
            'tipo' => 'subcategoria',
            'categoria_padre_id' => $category->id,
        ]);

        Productos::create([
            'nombre' => 'Coca Cola',
            'precio' => 10,
            'infraestructuras_tienda_id' => $this->shop->id,
            'categoria_id' => $subcat->id,
            'marca_id' => $brand->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->user)->delete("/cliente/marcas/{$brand->id}");
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('marcas', [
            'id' => $brand->id,
        ]);
    }
}
