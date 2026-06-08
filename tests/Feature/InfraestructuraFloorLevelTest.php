<?php

namespace Tests\Feature;

use App\Filament\Resources\Infraestructuras\Pages\CreateInfraestructurasCustom;
use App\Filament\Resources\Infraestructuras\Pages\EditInfraestructurasCustom;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\EstadoTienda;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class InfraestructuraFloorLevelTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    public function test_creating_infrastructure_auto_calculates_floor_levels(): void
    {
        // 1. Instantiate the livewire page for creating infrastructures custom
        $comp = Livewire::actingAs($this->adminUser)
            ->test(CreateInfraestructurasCustom::class);

        // 2. Initial state has 1 floor (defaults to name "Piso 1" and level/numero "Planta baja")
        $comp->assertSet('pisos.0.nombre', 'Piso 1')
            ->assertSet('pisos.0.numero', 'Planta baja');

        // 3. Add another floor
        $comp->call('addPiso');

        // 4. Second floor should default to name "Piso 2" and level/numero "Piso 1"
        $comp->assertSet('pisos.1.nombre', 'Piso 2')
            ->assertSet('pisos.1.numero', 'Piso 1');

        // 5. Fill other required values
        $comp->set('nombre', 'Mall Test MultiLevel')
            ->set('ubicacion', 'Av. Arce #100')
            ->set('lat', '-16.5000')
            ->set('long', '-68.1500');

        // 6. Submit the form
        $comp->call('save');

        // 7. Verify it persisted in the DB
        $infra = Infraestructuras::where('nombre', 'Mall Test MultiLevel')->first();
        $this->assertNotNull($infra);
        $this->assertEquals(2, $infra->pisos);

        $piso1 = $infra->pisosInfraestructura()->orderBy('id')->first();
        $this->assertEquals('Piso 1', $piso1->nombre);
        $this->assertEquals('Planta baja', $piso1->numero);
        $tienda = $piso1->tiendas()->first();
        $this->assertNotNull($tienda);
        $this->assertNull($tienda->nombre);
        $this->assertSame('1', $tienda->numero);
        $this->assertSame('+591 7000 0000', $tienda->telefono_referencia);
        $this->assertSame($this->adminUser->email, $tienda->email_contacto);
        $this->assertSame('20', $tienda->tamano);

        $piso2 = $infra->pisosInfraestructura()->orderBy('id')->skip(1)->first();
        $this->assertEquals('Piso 2', $piso2->nombre);
        $this->assertEquals('Piso 1', $piso2->numero);
    }

    public function test_infrastructure_form_hides_brand_assignment_and_shows_background_button(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateInfraestructurasCustom::class)
            ->assertSee('Elegir imagen de fondo')
            ->assertSee('Local disponible sin nombre comercial')
            ->assertSee('Email de contacto')
            ->assertDontSee('NOMBRE DE LA TIENDA')
            ->assertDontSee('Marcas Asociadas');
    }

    public function test_edit_infrastructure_prefills_store_contact_fields_and_preserves_commercial_name(): void
    {
        $infra = Infraestructuras::create([
            'nombre' => 'Mall Contactos Edit',
            'ubicacion' => 'Av. Contacto #10',
            'lat' => '-16.5000',
            'long' => '-68.1500',
            'pisos' => 1,
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'Lobby',
            'numero' => 'Planta baja',
            'cantidad_tiendas' => 1,
            'estado' => 'activo',
            'imagen_fondo' => '/images/backgrounds/bg_mall_white.jpg',
        ]);

        $tienda = InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'nombre' => 'Nombre Comercial Cliente',
            'numero' => 'A-101',
            'telefono_referencia' => '+591 7123 4567',
            'email_contacto' => 'local@mall.test',
            'tamano' => '45',
            'descripcion' => 'Apto para cocina',
            'id_estado' => 1,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditInfraestructurasCustom::class, ['record' => $infra->id])
            ->assertSet('pisos.0.tiendas.0.id', $tienda->id)
            ->assertSet('pisos.0.tiendas.0.numero', 'A-101')
            ->assertSet('pisos.0.tiendas.0.telefono_referencia', '+591 7123 4567')
            ->assertSet('pisos.0.tiendas.0.email_contacto', 'local@mall.test')
            ->assertSet('pisos.0.tiendas.0.tamano', '45')
            ->assertSet('pisos.0.tiendas.0.descripcion', 'Apto para cocina')
            ->assertDontSee('NOMBRE DE LA TIENDA')
            ->set('pisos.0.tiendas.0.numero', 'A-102')
            ->set('pisos.0.tiendas.0.telefono_referencia', '+591 7987 6543')
            ->set('pisos.0.tiendas.0.email_contacto', 'contacto-local@mall.test')
            ->set('pisos.0.tiendas.0.tamano', '50')
            ->set('pisos.0.tiendas.0.descripcion', 'No apto para cocina')
            ->call('save')
            ->assertHasNoErrors();

        $tienda->refresh();
        $this->assertSame('Nombre Comercial Cliente', $tienda->nombre);
        $this->assertSame('A-102', $tienda->numero);
        $this->assertSame('+591 7987 6543', $tienda->telefono_referencia);
        $this->assertSame('contacto-local@mall.test', $tienda->email_contacto);
        $this->assertSame('50', $tienda->tamano);
        $this->assertSame('No apto para cocina', $tienda->descripcion);
    }

    public function test_background_modal_assigns_existing_option_to_floor(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateInfraestructurasCustom::class)
            ->call('openBackgroundModal', 0)
            ->assertSet('backgroundModalOpen', true)
            ->call('selectBackground', '/images/backgrounds/bg_mall_dark.jpg')
            ->call('confirmBackgroundImage')
            ->assertSet('backgroundModalOpen', false)
            ->assertSet('pisos.0.imagen_fondo', '/images/backgrounds/bg_mall_dark.jpg');
    }

    public function test_background_modal_requires_explicit_selection_before_confirming(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateInfraestructurasCustom::class)
            ->call('openBackgroundModal', 0)
            ->assertSeeHtml('z-index: 2147483647')
            ->assertSeeHtml('backdrop-filter: blur(30px)')
            ->assertSeeHtml('-webkit-backdrop-filter: blur(30px)')
            ->assertSeeHtml('disabled')
            ->call('confirmBackgroundImage')
            ->assertSet('backgroundModalOpen', true)
            ->assertSet('pisos.0.imagen_fondo', '/images/backgrounds/bg_mall_white.jpg')
            ->assertHasErrors(['backgroundSelection']);
    }

    public function test_background_upload_is_stored_and_assigned_to_floor(): void
    {
        Storage::fake('public');

        $component = Livewire::actingAs($this->adminUser)
            ->test(CreateInfraestructurasCustom::class)
            ->call('openBackgroundModal', 0)
            ->set('backgroundUpload', UploadedFile::fake()->image('fondo-piso.jpg', 1200, 800))
            ->call('confirmBackgroundImage')
            ->assertSet('backgroundModalOpen', false);

        $backgroundUrl = $component->get('pisos.0.imagen_fondo');

        $this->assertStringStartsWith('/storage/infraestructuras/fondos/', $backgroundUrl);
        Storage::disk('public')->assertExists(Str::after($backgroundUrl, '/storage/'));
    }

    public function test_edit_background_confirmation_persists_existing_floor_immediately(): void
    {
        $infra = Infraestructuras::create([
            'nombre' => 'Mall Fondo Persistente',
            'ubicacion' => 'Av. Fondo #10',
            'lat' => '-16.5000',
            'long' => '-68.1500',
            'pisos' => 1,
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'Lobby',
            'numero' => 'Planta baja',
            'cantidad_tiendas' => 0,
            'estado' => 'activo',
            'imagen_fondo' => '/images/backgrounds/bg_mall_white.jpg',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditInfraestructurasCustom::class, ['record' => $infra->id])
            ->call('openBackgroundModal', 0)
            ->call('selectBackground', '/images/backgrounds/bg_mall_grey.jpg')
            ->call('confirmBackgroundImage')
            ->assertSet('backgroundModalOpen', false);

        $this->assertSame('/images/backgrounds/bg_mall_grey.jpg', $piso->refresh()->imagen_fondo);
    }

    public function test_edit_background_upload_persists_existing_floor_immediately(): void
    {
        Storage::fake('public');

        $infra = Infraestructuras::create([
            'nombre' => 'Mall Upload Persistente',
            'ubicacion' => 'Av. Upload #20',
            'lat' => '-16.5000',
            'long' => '-68.1500',
            'pisos' => 1,
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'Lobby',
            'numero' => 'Planta baja',
            'cantidad_tiendas' => 0,
            'estado' => 'activo',
            'imagen_fondo' => '/images/backgrounds/bg_mall_white.jpg',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditInfraestructurasCustom::class, ['record' => $infra->id])
            ->call('openBackgroundModal', 0)
            ->set('backgroundUpload', UploadedFile::fake()->image('fondo-unsplash.jpg', 1600, 1000))
            ->call('confirmBackgroundImage')
            ->assertSet('backgroundModalOpen', false);

        $backgroundUrl = $piso->refresh()->imagen_fondo;

        $this->assertStringStartsWith('/storage/infraestructuras/fondos/', $backgroundUrl);
        Storage::disk('public')->assertExists(Str::after($backgroundUrl, '/storage/'));
    }

    public function test_editing_and_customizing_floor_levels(): void
    {
        // 1. Create a base infrastructure and floors
        $infra = Infraestructuras::create([
            'nombre' => 'Edit Mall Test',
            'ubicacion' => 'Av. Arce #200',
            'lat' => '-16.5000',
            'long' => '-68.1500',
            'pisos' => 2,
        ]);

        $piso1 = InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'PB',
            'numero' => 'Planta baja',
            'cantidad_tiendas' => 1,
            'estado' => 'activo',
        ]);

        $piso2 = InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'P1',
            'numero' => 'Piso 1',
            'cantidad_tiendas' => 1,
            'estado' => 'activo',
        ]);

        // 2. Instantiate Edit Page
        $comp = Livewire::actingAs($this->adminUser)
            ->test(EditInfraestructurasCustom::class, ['record' => $infra->id]);

        $comp->assertSet('pisos.0.id', $piso1->id)
            ->assertSet('pisos.0.numero', 'Planta baja')
            ->assertSet('pisos.1.id', $piso2->id)
            ->assertSet('pisos.1.numero', 'Piso 1');

        // 3. Customize level name
        $comp->set('pisos.0.numero', 'Sótano -1')
            ->set('pisos.0.nombre', 'Zona de Autos')
            ->set('pisos.1.numero', 'Planta Principal')
            ->set('pisos.1.nombre', 'Zona Boutiques');

        // 4. Save
        $comp->call('save');

        // 5. Verify persisted changes
        $piso1->refresh();
        $this->assertEquals('Zona de Autos', $piso1->nombre);
        $this->assertEquals('Sótano -1', $piso1->numero);

        $piso2->refresh();
        $this->assertEquals('Zona Boutiques', $piso2->nombre);
        $this->assertEquals('Planta Principal', $piso2->numero);
    }

    public function test_welcome_page_includes_custom_floor_level_descriptions(): void
    {
        // 1. Create a base infrastructure and floor with level "Planta baja"
        $infra = Infraestructuras::create([
            'nombre' => 'Welcome Mall Test',
            'ubicacion' => 'Av. Arce #300',
            'lat' => '-16.5000',
            'long' => '-68.1500',
            'pisos' => 1,
        ]);

        InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'Lobby principal',
            'numero' => 'Planta baja',
            'cantidad_tiendas' => 0,
            'estado' => 'activo',
        ]);

        // 2. Query Welcome Controller
        $response = $this->get('/?infraestructura_id='.$infra->id.'&json=1');

        $response->assertStatus(200);
        $response->assertJsonPath('mall.floors.0.displayLevel', 'Planta baja');
        $response->assertJsonPath('mall.floors.0.name', 'Lobby principal');
    }

    public function test_public_pages_use_store_specific_contact_information(): void
    {
        $infra = Infraestructuras::create([
            'nombre' => 'Mall Contacto Público',
            'ubicacion' => 'Av. Pública #400',
            'lat' => '-16.5000',
            'long' => '-68.1500',
            'pisos' => 1,
        ]);

        $piso = InfraestructurasPisos::create([
            'infraestructura_id' => $infra->id,
            'nombre' => 'Lobby',
            'numero' => 'Planta baja',
            'cantidad_tiendas' => 1,
            'estado' => 'activo',
        ]);

        $disponible = EstadoTienda::firstOrCreate(['estado' => 'Disponible']);

        $tienda = InfraestructurasTiendas::create([
            'infraestructura_piso_id' => $piso->id,
            'nombre' => null,
            'numero' => 'D-101',
            'telefono_referencia' => '+591 7555 0001',
            'email_contacto' => 'contacto-tienda@mall.test',
            'tamano' => '33',
            'descripcion' => 'Apto para cocina',
            'id_estado' => $disponible->id,
        ]);

        $this->get('/?infraestructura_id='.$infra->id.'&json=1')
            ->assertOk()
            ->assertJsonPath('mall.floors.0.stores.0.telefono', '+591 7555 0001')
            ->assertJsonPath('mall.floors.0.stores.0.email_contacto', 'contacto-tienda@mall.test')
            ->assertJsonPath('mall.floors.0.stores.0.descripcion', 'Apto para cocina');

        $this->get('/suscripciones?tienda_id='.$tienda->id)
            ->assertOk()
            ->assertSee('+591 7555 0001');
    }
}
