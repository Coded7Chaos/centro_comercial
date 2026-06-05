<?php

namespace Tests\Feature;

use App\Filament\Resources\Suscripciones\SuscripcionesResource;
use App\Models\Clientes;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesTarifas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SuscripcionesPermissionsAndPdfTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $client;
    protected $shop;
    protected $subscription;
    protected $piso;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Create client user and client relation
        $clientUser = User::factory()->create();
        $clientUser->assignRole('cliente');
        $this->client = Clientes::create([
            'user_id' => $clientUser->id,
            'ci' => '8888888',
            'numero_celular' => '78888888',
            'genero' => 'masculino',
        ]);

        // Create infrastructure, floor, and shop
        $infra = Infraestructuras::create([
            'nombre' => 'Marble Gallery Test',
            'ubicacion' => 'La Paz Centro',
            'pisos' => 3,
        ]);

        $this->piso = InfraestructurasPisos::create([
            'nombre' => 'Piso 3 - Sky Lounge',
            'numero' => 'Piso 3',
            'infraestructura_id' => $infra->id,
        ]);

        $this->shop = InfraestructurasTiendas::create([
            'numero' => 301,
            'tamano' => '87',
            'id_estado' => 1,
            'infraestructura_piso_id' => $this->piso->id,
        ]);

        $fee = SuscripcionesTarifas::create([
            'tamano_min' => 50,
            'tamano_max' => 100,
            'etiqueta' => 'Tarifa Premium',
            'tipo' => 'mensual',
            'precio' => 2000.00,
        ]);

        $this->subscription = Suscripciones::create([
            'cliente_id' => $this->client->id,
            'infraestructuras_tienda_id' => $this->shop->id,
            'suscripciones_tarifa_id' => $fee->id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-08-31',
            'estado' => 'activo',
            'tipo' => '3 meses',
            'precio' => 2000.00,
        ]);
    }

    /**
     * Test that contract deletion permissions are disabled in the resource.
     */
    public function test_suscripciones_deletion_is_disabled(): void
    {
        $this->assertFalse(SuscripcionesResource::canDelete($this->subscription));
        $this->assertFalse(SuscripcionesResource::canDeleteAny());
    }

    /**
     * Test that the PDF view renders details correctly without duplicate floor prefix and with size unit.
     */
    public function test_contract_pdf_view_renders_details_correctly(): void
    {
        $view = $this->view('pdf.contrato', [
            'suscripcion' => $this->subscription,
            'cliente' => $this->client,
            'marca' => null,
            'tienda' => $this->shop,
            'piso' => $this->piso,
            'infraestructura' => $this->piso->infraestructura,
            'fechaDocumento' => '05 de junio de 2026',
        ]);

        // Verify floor is printed as "Piso 3 - Sky Lounge" instead of duplicating Piso (like Piso Piso 3)
        $view->assertSee('Piso 3');
        $view->assertSee('Sky Lounge');
        $view->assertDontSee('Piso Piso 3');

        // Verify shop size displays as "87 m^2"
        $view->assertSee('87 m^2');
    }
}
