<?php

namespace Tests\Feature;

use App\Filament\Resources\Marcas\Pages\EditMarcas;
use App\Filament\Resources\Marcas\Pages\ListMarcas;
use App\Filament\Resources\Marcas\MarcasResource;
use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\User;
use App\Support\ActiveInfraestructura;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBrandDeletionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_delete_brand_from_table_with_comment_and_sends_notification(): void
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

        $marca = Marcas::create([
            'nombre' => 'Marca de Pruebas',
            'estado' => 'activo',
            'cliente_id' => $cliente->id,
            'logo' => 'marcas-logos/test_logo.jpg'
        ]);

        Livewire::actingAs($superAdmin)
            ->test(ListMarcas::class)
            ->callTableAction('delete', $marca, [
                'comentario' => 'Esta marca no cumple con las políticas.'
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('marcas', [
            'id' => $marca->id,
        ]);

        $this->assertDatabaseHas('client_notifications', [
            'cliente_id' => $cliente->id,
            'titulo' => 'Un administrador ha eliminado tu marca',
            'mensaje' => 'Tu marca "Marca de Pruebas" ha sido eliminada por un administrador. Razón: Esta marca no cumple con las políticas.',
        ]);
    }

    public function test_super_admin_delete_brand_blocked_if_has_products(): void
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

        $marca = Marcas::create([
            'nombre' => 'Marca con Productos',
            'estado' => 'activo',
            'cliente_id' => $cliente->id,
        ]);

        $infraestructura = Infraestructuras::create([
            'nombre' => 'Mall Test',
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
            'numero' => 'T-999',
            'nombre' => 'Tienda Test',
            'tamano' => '20',
        ]);

        $categoria = \App\Models\Categorias::create([
            'nombre' => 'Alimentos',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);

        // Create product associated with the brand
        $producto = Productos::create([
            'nombre' => 'Producto de Prueba',
            'precio' => 10,
            'infraestructuras_tienda_id' => $tienda->id,
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'estado' => 'activo',
        ]);

        Livewire::actingAs($superAdmin)
            ->test(ListMarcas::class)
            ->callTableAction('delete', $marca)
            ->assertHasNoTableActionErrors();

        // The brand should NOT be deleted
        $this->assertDatabaseHas('marcas', [
            'id' => $marca->id,
            'deleted_at' => null,
        ]);
    }

    public function test_super_admin_can_delete_brand_from_edit_page_with_comment_and_sends_notification(): void
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

        $marca = Marcas::create([
            'nombre' => 'Marca Edit Deletion',
            'estado' => 'activo',
            'cliente_id' => $cliente->id,
            'logo' => 'marcas-logos/test_logo_edit.jpg'
        ]);

        Livewire::actingAs($superAdmin)
            ->test(EditMarcas::class, ['record' => $marca->getRouteKey()])
            ->callAction('delete', [
                'comentario' => 'Solicitud del cliente.'
            ])
            ->assertHasNoActionErrors()
            ->assertRedirect(MarcasResource::getUrl('index'));

        $this->assertSoftDeleted('marcas', [
            'id' => $marca->id,
        ]);

        $this->assertDatabaseHas('client_notifications', [
            'cliente_id' => $cliente->id,
            'titulo' => 'Un administrador ha eliminado tu marca',
            'mensaje' => 'Tu marca "Marca Edit Deletion" ha sido eliminada por un administrador. Razón: Solicitud del cliente.',
        ]);
    }
}
