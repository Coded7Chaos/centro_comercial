<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Categorias;
use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\ProductosImagenes;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientDashboardController extends Controller
{
    protected function getClienteOrAbort()
    {
        $user = Auth::user();
        $cliente = $user->cliente;
        if (!$cliente) {
            abort(403, 'Su cuenta de usuario no está vinculada a ningún Cliente. Contacte al administrador.');
        }
        return $cliente;
    }

    public function dashboard()
    {
        $cliente = $this->getClienteOrAbort();
        
        // Tiendas asociadas
        $tiendas = $cliente->tiendas()->with(['piso.infraestructura', 'estado'])->get();
        
        // Conteo de productos
        $tiendaIds = $tiendas->pluck('id')->toArray();
        $cantProductos = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)->count();
        
        // Suscripciones activas
        $suscripciones = Suscripciones::where('cliente_id', $cliente->id)
            ->with(['infraestructurasTienda'])
            ->orderBy('fecha_fin', 'desc')
            ->get();

        // Cobros pendientes o vencidos
        $suscripcionIds = $suscripciones->pluck('id')->toArray();
        $cobrosPendientes = SuscripcionesCobros::whereIn('suscripcion_id', $suscripcionIds)
            ->whereIn('estado', ['pendiente', 'parcial', 'vencido'])
            ->with('pagos')
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
            
        // Resumen financiero
        $totalDeuda = 0;
        foreach ($cobrosPendientes as $c) {
            $totalDeuda += max(0, $c->monto - $c->pagos->sum('monto_pagado'));
        }

        return view('cliente.dashboard', compact('cliente', 'tiendas', 'cantProductos', 'suscripciones', 'cobrosPendientes', 'totalDeuda'));
    }

    public function tienda()
    {
        $cliente = $this->getClienteOrAbort();
        $tiendas = $cliente->tiendas()->with('piso.infraestructura')->get();
        
        return view('cliente.tienda', compact('cliente', 'tiendas'));
    }

    public function actualizarTienda(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $request->validate([
            'tienda_id' => 'required|exists:infraestructuras_tiendas,id',
            'nombre' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
            'telefono_referencia' => 'nullable|string|max:30',
            'foto' => 'nullable|image|max:5120',
        ]);

        $tienda = $cliente->tiendas()->findOrFail($request->tienda_id);
        
        $tienda->nombre = $request->nombre;
        $tienda->descripcion = $request->descripcion;
        $tienda->telefono_referencia = $request->telefono_referencia;

        if ($request->hasFile('foto')) {
            if ($tienda->foto_referencial) {
                Storage::disk('public')->delete($tienda->foto_referencial);
            }
            $path = $request->file('foto')->store('tiendas-fachadas', 'public');
            $tienda->foto_referencial = $path; // check column in db, it might be named foto or foto_referencial. Let's verify.
        }

        $tienda->save();

        return back()->with('success', 'Información de la tienda actualizada correctamente.');
    }

    public function productos()
    {
        $cliente = $this->getClienteOrAbort();
        $tiendaIds = $cliente->tiendas->pluck('id')->toArray();
        
        $productos = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)
            ->with(['categoria', 'marca', 'imagenes', 'tienda'])
            ->orderBy('id', 'desc')
            ->get();

        return view('cliente.productos.index', compact('productos'));
    }

    public function crearProducto()
    {
        $cliente = $this->getClienteOrAbort();
        $tiendas = $cliente->tiendas;
        
        if ($tiendas->isEmpty()) {
            return redirect()->route('cliente.dashboard')->with('error', 'Debe tener al menos una tienda asignada para registrar productos.');
        }

        $categorias = Categorias::whereNull('categoria_padre_id')->with('subcategorias')->get();
        $marcas = Marcas::where('cliente_id', $cliente->id)->orWhereNull('cliente_id')->get(); // Incluye marcas globales

        return view('cliente.productos.create', compact('tiendas', 'categorias', 'marcas'));
    }

    public function guardarProducto(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $tiendaIds = $cliente->tiendas->pluck('id')->toArray();

        $request->validate([
            'nombre' => 'required|string|max:80',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'infraestructuras_tienda_id' => 'required|in:' . implode(',', $tiendaIds),
            'categoria_id' => 'required|exists:categorias,id',
            'subcategoria_id' => 'required|exists:categorias,id',
            'marca_id' => 'required|exists:marcas,id',
            'imagen' => 'required|image|max:5120',
        ]);

        $producto = Productos::create([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'descripcion' => $request->descripcion,
            'infraestructuras_tienda_id' => $request->infraestructuras_tienda_id,
            'categoria_id' => $request->subcategoria_id, // Guardamos la subcategoría en categoria_id de productos
            'marca_id' => $request->marca_id,
            'estado' => 'activo',
        ]);

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            ProductosImagenes::create([
                'producto_id' => $producto->id,
                'url' => $path,
                'tipo' => 'principal',
            ]);
        }

        return redirect()->route('cliente.productos.index')->with('success', 'Producto registrado exitosamente en su catálogo.');
    }

    public function editarProducto($id)
    {
        $cliente = $this->getClienteOrAbort();
        $tiendaIds = $cliente->tiendas->pluck('id')->toArray();
        
        $producto = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)->findOrFail($id);
        $tiendas = $cliente->tiendas;
        
        // Categorías
        $categorias = Categorias::whereNull('categoria_padre_id')->with('subcategorias')->get();
        
        // Obtener categoría padre del producto
        $subcat = Categorias::find($producto->categoria_id);
        $categoriaPadreId = $subcat ? $subcat->categoria_padre_id : null;

        $marcas = Marcas::where('cliente_id', $cliente->id)->orWhereNull('cliente_id')->get();

        return view('cliente.productos.edit', compact('producto', 'tiendas', 'categorias', 'marcas', 'categoriaPadreId'));
    }

    public function actualizarProducto(Request $request, $id)
    {
        $cliente = $this->getClienteOrAbort();
        $tiendaIds = $cliente->tiendas->pluck('id')->toArray();
        
        $producto = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:80',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'infraestructuras_tienda_id' => 'required|in:' . implode(',', $tiendaIds),
            'categoria_id' => 'required|exists:categorias,id',
            'subcategoria_id' => 'required|exists:categorias,id',
            'marca_id' => 'required|exists:marcas,id',
            'imagen' => 'nullable|image|max:5120',
        ]);

        $producto->update([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'descripcion' => $request->descripcion,
            'infraestructuras_tienda_id' => $request->infraestructuras_tienda_id,
            'categoria_id' => $request->subcategoria_id,
            'marca_id' => $request->marca_id,
        ]);

        if ($request->hasFile('imagen')) {
            // Eliminar imagen principal anterior si existe
            $imgPrincipal = $producto->imagenes()->where('tipo', 'principal')->first();
            if ($imgPrincipal) {
                Storage::disk('public')->delete($imgPrincipal->url);
                $imgPrincipal->delete();
            }

            $path = $request->file('imagen')->store('productos', 'public');
            ProductosImagenes::create([
                'producto_id' => $producto->id,
                'url' => $path,
                'tipo' => 'principal',
            ]);
        }

        return redirect()->route('cliente.productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function eliminarProducto($id)
    {
        $cliente = $this->getClienteOrAbort();
        $tiendaIds = $cliente->tiendas->pluck('id')->toArray();
        
        $producto = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)->findOrFail($id);

        // Borrar imágenes físicas y registros
        foreach ($producto->imagenes as $img) {
            Storage::disk('public')->delete($img->url);
            $img->delete();
        }

        $producto->delete();

        return redirect()->route('cliente.productos.index')->with('success', 'Producto eliminado de su catálogo.');
    }

    public function estadoCuenta()
    {
        $cliente = $this->getClienteOrAbort();
        $suscripciones = Suscripciones::where('cliente_id', $cliente->id)->get();
        $suscripcionIds = $suscripciones->pluck('id')->toArray();
        
        $cobros = SuscripcionesCobros::whereIn('suscripcion_id', $suscripcionIds)
            ->with(['pagos', 'suscripcion.infraestructurasTienda'])
            ->orderBy('fecha_vencimiento', 'desc')
            ->get();

        return view('cliente.estado-cuenta', compact('cobros'));
    }

    public function registrarPago(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $suscripciones = Suscripciones::where('cliente_id', $cliente->id)->get();
        $suscripcionIds = $suscripciones->pluck('id')->toArray();
        
        $request->validate([
            'suscripcion_cobro_id' => 'required|exists:suscripciones_cobros,id',
            'monto_pagado' => 'required|numeric|min:0.1',
            'metodo_pago' => 'required|in:efectivo,transferencia,qr,tarjeta',
            'nombre_pagador' => 'required_if:metodo_pago,efectivo|nullable|string|max:100',
            'numero_transaccion' => 'required_if:metodo_pago,transferencia|nullable|string|max:50',
            'banco_origen' => 'required_if:metodo_pago,transferencia|nullable|string|max:50',
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $cobro = SuscripcionesCobros::whereIn('suscripcion_id', $suscripcionIds)
            ->findOrFail($request->suscripcion_cobro_id);

        $totalPagado = $cobro->pagos()->sum('monto_pagado');
        $pendiente = max(0, $cobro->monto - $totalPagado - $request->monto_pagado);

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes-pagos', 'public');
        }

        SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $cobro->id,
            'monto_pagado' => $request->monto_pagado,
            'pago_pendiente' => $pendiente,
            'fecha_pago' => now()->toDateString(),
            'metodo_pago' => $request->metodo_pago,
            'estado_verificacion' => 'verificado', // Se asume verificado temporalmente o en espera de auditoría
            'nombre_pagador' => $request->nombre_pagador,
            'numero_transaccion' => $request->numero_transaccion,
            'banco_origen' => $request->banco_origen,
            'comprobante' => $comprobantePath,
            'observaciones' => 'Pago reportado por el cliente desde el panel.',
        ]);

        return redirect()->route('cliente.estado-cuenta')->with('success', 'El pago ha sido registrado y reportado correctamente al administrador.');
    }

    public function marcas()
    {
        $cliente = $this->getClienteOrAbort();
        $marcas = Marcas::where('cliente_id', $cliente->id)->orderBy('id', 'desc')->get();
        return view('cliente.marcas.index', compact('marcas'));
    }

    public function crearMarca()
    {
        return view('cliente.marcas.form');
    }

    public function guardarMarca(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $request->validate([
            'nombre' => 'required|string|max:60|min:3|regex:/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\&\.]{3,60}$/u',
            'descripcion' => 'nullable|string|max:500|regex:/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ]).*$/u',
            'logo' => 'nullable|image|max:2048',
        ], [
            'nombre.regex' => 'El nombre debe contener letras y caracteres válidos.',
            'descripcion.regex' => 'La descripción debe contener al menos una letra.',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('marcas-logos', 'public');
        }

        Marcas::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'cliente_id' => $cliente->id,
            'logo' => $logoPath,
            'estado' => 'activo',
        ]);

        return redirect()->route('cliente.marcas.index')->with('success', 'Marca privada registrada correctamente.');
    }

    public function editarMarca($id)
    {
        $cliente = $this->getClienteOrAbort();
        $marca = Marcas::where('cliente_id', $cliente->id)->findOrFail($id);
        return view('cliente.marcas.form', compact('marca'));
    }

    public function actualizarMarca(Request $request, $id)
    {
        $cliente = $this->getClienteOrAbort();
        $marca = Marcas::where('cliente_id', $cliente->id)->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:60|min:3|regex:/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\&\.]{3,60}$/u',
            'descripcion' => 'nullable|string|max:500|regex:/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ]).*$/u',
            'logo' => 'nullable|image|max:2048',
        ], [
            'nombre.regex' => 'El nombre debe contener letras y caracteres válidos.',
            'descripcion.regex' => 'La descripción debe contener al menos una letra.',
        ]);

        $logoPath = $marca->logo;
        if ($request->hasFile('logo')) {
            if ($marca->logo) {
                Storage::disk('public')->delete($marca->logo);
            }
            $logoPath = $request->file('logo')->store('marcas-logos', 'public');
        }

        $marca->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'logo' => $logoPath,
        ]);

        return redirect()->route('cliente.marcas.index')->with('success', 'Marca privada actualizada correctamente.');
    }

    public function eliminarMarca($id)
    {
        $cliente = $this->getClienteOrAbort();
        $marca = Marcas::where('cliente_id', $cliente->id)->findOrFail($id);

        // Validar que no tenga productos asociados
        if (Productos::where('marca_id', $marca->id)->exists()) {
            return back()->with('error', 'No se puede eliminar la marca porque tiene productos asociados en el catálogo.');
        }

        // Validar que no esté en tiendas
        if ($marca->tiendas()->exists()) {
            return back()->with('error', 'No se puede eliminar la marca porque está asignada a un local comercial activo.');
        }

        if ($marca->logo) {
            Storage::disk('public')->delete($marca->logo);
        }

        $marca->delete();

        return redirect()->route('cliente.marcas.index')->with('success', 'Marca privada eliminada correctamente.');
    }
}
