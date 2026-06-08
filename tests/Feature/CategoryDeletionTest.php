<?php

namespace Tests\Feature;

use App\Models\Categorias;
use App\Models\Marcas;
use App\Models\Productos;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_deleting_parent_category_deletes_subcategories_and_clears_product_category(): void
    {
        $parent = Categorias::create([
            'nombre' => 'Gastronomía',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);

        $subcategory = Categorias::create([
            'nombre' => 'Café',
            'tipo' => 'subcategoria',
            'estado' => 'activo',
            'categoria_padre_id' => $parent->id,
        ]);

        $product = $this->createProductForCategory($subcategory);

        $parent->delete();

        $this->assertDatabaseMissing('categorias', ['id' => $parent->id]);
        $this->assertDatabaseMissing('categorias', ['id' => $subcategory->id]);
        $this->assertDatabaseHas('productos', [
            'id' => $product->id,
            'categoria_id' => null,
        ]);
    }

    public function test_deleting_subcategory_only_clears_that_product_category(): void
    {
        $parent = Categorias::create([
            'nombre' => 'Servicios',
            'tipo' => 'categoria',
            'estado' => 'activo',
        ]);

        $subcategory = Categorias::create([
            'nombre' => 'Lavandería',
            'tipo' => 'subcategoria',
            'estado' => 'activo',
            'categoria_padre_id' => $parent->id,
        ]);

        $product = $this->createProductForCategory($subcategory);

        $subcategory->delete();

        $this->assertDatabaseHas('categorias', ['id' => $parent->id]);
        $this->assertDatabaseMissing('categorias', ['id' => $subcategory->id]);
        $this->assertDatabaseHas('productos', [
            'id' => $product->id,
            'categoria_id' => null,
        ]);
    }

    private function createProductForCategory(Categorias $category): Productos
    {
        $brand = Marcas::create([
            'nombre' => 'Marca Test',
            'estado' => 'activo',
        ]);

        return Productos::create([
            'nombre' => 'Producto Test',
            'precio' => 10,
            'descripcion' => 'Producto para verificar borrado de categorías.',
            'categoria_id' => $category->id,
            'marca_id' => $brand->id,
            'estado' => 'activo',
        ]);
    }
}
