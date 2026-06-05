<?php

namespace Tests\Feature;

use App\Filament\Resources\Categorias\CategoriasResource;
use App\Models\Categorias;
use App\Models\Infraestructuras;
use App\Models\InfraestructurasPisos;
use App\Models\InfraestructurasTiendas;
use App\Models\Productos;
use App\Support\ActiveInfraestructura;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoriasFiltradoTest extends TestCase
{
    use DatabaseTransactions;

    public function test_categories_filtered_by_active_infrastructure(): void
    {
        // 1. Create infrastructures
        $infraA = Infraestructuras::create(['nombre' => 'Infra A', 'pisos' => 1, 'ubicacion' => 'Loc A']);
        $infraB = Infraestructuras::create(['nombre' => 'Infra B', 'pisos' => 1, 'ubicacion' => 'Loc B']);

        // 2. Create floors (pisos)
        $pisoA = InfraestructurasPisos::create(['nombre' => 'Piso A', 'infraestructura_id' => $infraA->id]);
        $pisoB = InfraestructurasPisos::create(['nombre' => 'Piso B', 'infraestructura_id' => $infraB->id]);

        // 3. Create shops (tiendas)
        $tiendaA = InfraestructurasTiendas::create([
            'numero' => 101,
            'tamano' => '10x10',
            'id_estado' => 1,
            'infraestructura_piso_id' => $pisoA->id,
        ]);
        $tiendaB = InfraestructurasTiendas::create([
            'numero' => 102,
            'tamano' => '10x10',
            'id_estado' => 1,
            'infraestructura_piso_id' => $pisoB->id,
        ]);

        // 4. Create a dummy brand for product association
        $brand = \App\Models\Marcas::create([
            'nombre' => 'Test Brand',
            'estado' => 'activo',
        ]);

        // 5. Create categories directly linked to infrastructure
        $catA = Categorias::create([
            'nombre' => 'Cat A (Linked to A)',
            'tipo' => 'categoria',
            'estado' => 'activo',
            'infraestructura_id' => $infraA->id,
        ]);
        $catB = Categorias::create([
            'nombre' => 'Cat B (Linked to B)',
            'tipo' => 'categoria',
            'estado' => 'activo',
            'infraestructura_id' => $infraB->id,
        ]);

        // 6. Create category linked via product in shop of infra A
        $catC = Categorias::create([
            'nombre' => 'Cat C (Used in A)',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);
        Productos::create([
            'nombre' => 'Prod en Tienda A',
            'precio' => 150.00,
            'categoria_id' => $catC->id,
            'infraestructuras_tienda_id' => $tiendaA->id,
            'marca_id' => $brand->id,
            'estado' => 'activo',
        ]);

        // 7. Create category linked via subcategory product in shop of infra A
        $catParent = Categorias::create([
            'nombre' => 'Cat Parent (Sub in A)',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);
        $subcat = Categorias::create([
            'nombre' => 'Subcat (Used in A)',
            'tipo' => 'subcategoria',
            'categoria_padre_id' => $catParent->id,
            'estado' => 'activo',
        ]);
        Productos::create([
            'nombre' => 'Prod en Tienda A 2',
            'precio' => 200.00,
            'categoria_id' => $subcat->id,
            'infraestructuras_tienda_id' => $tiendaA->id,
            'marca_id' => $brand->id,
            'estado' => 'activo',
        ]);

        // 7. Verify query when Infra A is active
        session([ActiveInfraestructura::SESSION_KEY => $infraA->id]);

        $results = CategoriasResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($catA->id, $results);
        $this->assertNotContains($catB->id, $results);
        $this->assertContains($catC->id, $results);
        $this->assertContains($catParent->id, $results);
        $this->assertContains($subcat->id, $results);

        // 8. Verify query when Infra B is active
        session([ActiveInfraestructura::SESSION_KEY => $infraB->id]);

        $resultsB = CategoriasResource::getEloquentQuery()->pluck('id')->all();

        $this->assertNotContains($catA->id, $resultsB);
        $this->assertContains($catB->id, $resultsB);
        $this->assertNotContains($catC->id, $resultsB);
        $this->assertNotContains($catParent->id, $resultsB);
        $this->assertNotContains($subcat->id, $resultsB);
    }
}
