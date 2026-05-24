<?php

namespace App\Http\Controllers;

use App\Models\InfraestructurasTiendas;
use App\Models\Productos;
use Illuminate\Http\Request;

class DirectorioController extends Controller
{
    public function index()
    {
        // Listar tiendas que están alquiladas (tienen cliente o marca)
        $tiendas = InfraestructurasTiendas::whereNotNull('marca_id')
            ->with(['marca', 'piso'])
            ->get();
            
        return view('directorio.index', compact('tiendas'));
    }

    public function catalogo($id)
    {
        $tienda = InfraestructurasTiendas::with(['marca'])->findOrFail($id);
        
        // Asumiendo que los productos se asocian a la marca o a la tienda (usaremos marca para ser flexibles)
        $productos = Productos::where('marca_id', $tienda->marca_id)
            ->with('imagenes')
            ->get();
            
        return view('directorio.catalogo', compact('tienda', 'productos'));
    }
}
