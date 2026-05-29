<?php

namespace Tests\Feature;

use App\Models\Clientes;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper to create a user and associate it with a client record.
     */
    protected function createClientUser()
    {
        $user = User::factory()->create();
        $user->assignRole('cliente');
        
        $client = Clientes::create([
            'user_id' => $user->id,
            'ci' => '1234567',
            'numero_celular' => '76543210',
            'genero' => 'masculino',
        ]);

        return [$user, $client];
    }

    /**
     * Guest access to client dashboard redirects to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/cliente/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * User without client association receives 403.
     */
    public function test_user_without_client_relation_gets_403(): void
    {
        $user = User::factory()->create();
        // Logged in but has no client relation in table Clientes
        $response = $this->actingAs($user)->get('/cliente/dashboard');
        $response->assertStatus(403);
    }

    /**
     * General dashboard route (/dashboard-cliente) redirects to client dashboard.
     */
    public function test_dashboard_cliente_redirects_to_cliente_dashboard(): void
    {
        [$user, $client] = $this->createClientUser();

        $response = $this->actingAs($user)->get('/dashboard-cliente');
        $response->assertRedirect('/cliente/dashboard');
    }

    /**
     * Client user can access all dashboard routes.
     */
    public function test_authorized_client_can_access_portal_routes(): void
    {
        [$user, $client] = $this->createClientUser();

        $this->actingAs($user);

        // GET /cliente/dashboard
        $response = $this->get('/cliente/dashboard');
        $response->assertStatus(200);

        // GET /cliente/tienda
        $response = $this->get('/cliente/tienda');
        $response->assertStatus(200);

        // GET /cliente/productos
        $response = $this->get('/cliente/productos');
        $response->assertStatus(200);

        // GET /cliente/marcas
        $response = $this->get('/cliente/marcas');
        $response->assertStatus(200);

        // GET /cliente/estado-cuenta
        $response = $this->get('/cliente/estado-cuenta');
        $response->assertStatus(200);
    }
}
