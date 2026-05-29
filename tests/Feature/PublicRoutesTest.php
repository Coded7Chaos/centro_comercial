<?php

namespace Tests\Feature;

use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test the welcome/landing page.
     */
    public function test_welcome_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test the login page.
     */
    public function test_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * Test the forgot password page.
     */
    public function test_forgot_password_page(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    /**
     * Test the directory page.
     */
    public function test_directory_page(): void
    {
        $response = $this->get('/directorio');
        $response->assertStatus(200);
    }

    /**
     * Test the subscriptions public info page.
     */
    public function test_subscriptions_page(): void
    {
        $response = $this->get('/suscripciones');
        $response->assertStatus(200);
    }

    /**
     * Test the products public info page.
     */
    public function test_products_page(): void
    {
        $response = $this->get('/productos');
        $response->assertStatus(200);
    }

    /**
     * Test the directory catalog page with non-existing shop.
     */
    public function test_directory_catalog_not_found(): void
    {
        $response = $this->get('/directorio/99999/catalogo');
        $response->assertStatus(404);
    }

    /**
     * Test the directory catalog page with an existing shop.
     */
    public function test_directory_catalog_existing(): void
    {
        // Find or create a brand
        $brand = Marcas::first();
        if (!$brand) {
            $brand = Marcas::create([
                'nombre' => 'Test Brand',
                'estado' => 'activo',
            ]);
        }

        // Find or create a shop
        $shop = InfraestructurasTiendas::first();
        if (!$shop) {
            // Find a floor or create one first, but since seeders run we can grab one
            $shop = InfraestructurasTiendas::create([
                'numero' => 999,
                'tamano' => '10x10',
                'id_estado' => 1,
            ]);
        }
        $shop->marcas()->sync([$brand->id]);

        $response = $this->get("/directorio/{$shop->id}/catalogo");
        $response->assertStatus(200);
    }
}
