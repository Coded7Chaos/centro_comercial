<?php

namespace App\Http\Controllers;

use App\Models\InfraestructurasTiendas;
use App\Models\Productos;
use Illuminate\Http\Request;

class DirectorioController extends Controller
{
    public function index()
    {
        // Listar tiendas que están alquiladas (tienen marcas asignadas)
        $tiendas = InfraestructurasTiendas::whereHas('marcas')
            ->with(['marcas', 'piso'])
            ->get();
            
        return view('directorio.index', compact('tiendas'));
    }

    public function catalogo($id)
    {
        $tienda = InfraestructurasTiendas::with(['marcas'])->findOrFail($id);
        
        // Asumiendo que los productos se asocian a las marcas de la tienda
        $marcaIds = $tienda->marcas->pluck('id')->toArray();
        $productos = Productos::whereIn('marca_id', $marcaIds)
            ->with('imagenes')
            ->get();
            
        return view('directorio.catalogo', compact('tienda', 'productos'));
    }
}
