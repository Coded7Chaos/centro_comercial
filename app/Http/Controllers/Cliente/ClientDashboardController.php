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

        // Gráficos de productos por categoría y por marca
        $productosPorCategoria = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)
            ->with('categoria')
            ->selectRaw('categoria_id, count(*) as total')
            ->groupBy('categoria_id')
            ->get()
            ->map(fn($p) => [
                'nombre' => $p->categoria ? $p->categoria->nombre : 'Sin Categoría',
                'total' => (int) $p->total
            ]);

        $productosPorMarca = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)
            ->with('marca')
            ->selectRaw('marca_id, count(*) as total')
            ->groupBy('marca_id')
            ->get()
            ->map(fn($p) => [
                'nombre' => $p->marca ? $p->marca->nombre : 'Sin Marca',
                'total' => (int) $p->total
            ]);

        return view('cliente.dashboard', compact(
            'cliente', 
            'tiendas', 
            'cantProductos', 
            'suscripciones', 
            'cobrosPendientes', 
            'totalDeuda',
            'productosPorCategoria',
            'productosPorMarca'
        ));
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
            ->with(['categoria.padre', 'marca', 'imagenes', 'tienda'])
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

        $settings = \App\Models\PaymentSettings::first();

        return view('cliente.estado-cuenta', compact('cobros', 'settings'));
    }

    public function registrarPago(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $suscripciones = Suscripciones::where('cliente_id', $cliente->id)->get();
        $suscripcionIds = $suscripciones->pluck('id')->toArray();
        
        $request->validate([
            'suscripcion_cobro_id' => 'required|exists:suscripciones_cobros,id',
            'metodo_pago' => 'required|in:transferencia,qr',
            'numero_transaccion' => 'required_if:metodo_pago,transferencia|nullable|string|max:50',
            'banco_origen' => 'required_if:metodo_pago,transferencia|nullable|string|max:50',
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $cobro = SuscripcionesCobros::whereIn('suscripcion_id', $suscripcionIds)
            ->findOrFail($request->suscripcion_cobro_id);

        // Check if there is already a pending request for this cobro
        $hasPending = SuscripcionesPagos::where('suscripcion_cobro_id', $cobro->id)
            ->where('estado_verificacion', 'pendiente')
            ->exists();
        if ($hasPending) {
            return redirect()->route('cliente.estado-cuenta')->with('error', 'Ya tiene una solicitud de pago pendiente para este cobro.');
        }

        $pagado = $cobro->pagos()->where('estado_verificacion', 'verificado')->sum('monto_pagado');
        $restante = max(0, $cobro->monto - $pagado);

        if ($restante <= 0) {
            return redirect()->route('cliente.estado-cuenta')->with('error', 'Este cobro ya se encuentra totalmente pagado.');
        }

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes-pagos', 'public');
        }

        $pago = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $cobro->id,
            'monto_pagado' => $restante, // Fixed payment amount
            'pago_pendiente' => 0,
            'fecha_pago' => now()->toDateString(),
            'metodo_pago' => $request->metodo_pago,
            'estado_verificacion' => 'pendiente',
            'numero_transaccion' => $request->metodo_pago === 'transferencia' ? $request->numero_transaccion : null,
            'banco_origen' => $request->metodo_pago === 'transferencia' ? $request->banco_origen : null,
            'comprobante' => $comprobantePath,
            'observaciones' => 'Solicitud de pago reportada por el cliente desde el panel.',
            'estado_snapshot' => 'pendiente',
            'creado_por_admin' => false,
        ]);

        // Recalculate status of the cobro
        $cobro->recalcularEstado();

        return redirect()->route('cliente.estado-cuenta')->with('success', 'Su solicitud de pago ha sido enviada y se encuentra pendiente de confirmación.');
    }

    public function marcarNotificacionLeida($id)
    {
        $cliente = $this->getClienteOrAbort();
        $notif = \App\Models\ClientNotification::where('cliente_id', $cliente->id)->findOrFail($id);
        $notif->update(['leido' => true]);

        return redirect()->back()->with('success', 'Notificación descartada.');
    }

    public function marcas()
    {
        $cliente = $this->getClienteOrAbort();

        // Marcas propias del cliente
        $misMarcas = Marcas::where('cliente_id', $cliente->id)
            ->orderBy('nombre')
            ->get();

        // Marcas públicas creadas por administradores (sin cliente asignado)
        $marcasAdmin = Marcas::whereNull('cliente_id')
            ->orderBy('nombre')
            ->get();

        return view('cliente.marcas.index', compact('misMarcas', 'marcasAdmin'));
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

    public function personalizar(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $tiendas = $cliente->tiendas()->with(['piso.infraestructura', 'marcas', 'productos.imagenes'])->get();
        
        $selectedTiendaId = $request->input('tienda_id');
        $tienda = null;
        if ($selectedTiendaId) {
            $tienda = $tiendas->firstWhere('id', $selectedTiendaId);
        }
        if (!$tienda && $tiendas->isNotEmpty()) {
            $tienda = $tiendas->first();
        }

        $productosVitrina = $tienda ? $tienda->productos()->with(['imagenes', 'categoria', 'marca'])->take(3)->get() : collect();
        
        $categorias = Categorias::whereNull('categoria_padre_id')->with('subcategorias')->get();
        $marcas = Marcas::where('cliente_id', $cliente->id)->orWhereNull('cliente_id')->get();

        return view('cliente.personalizar', compact('cliente', 'tiendas', 'tienda', 'productosVitrina', 'categorias', 'marcas'));
    }

    public function actualizarTiendaPersonalizar(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $request->validate([
            'tienda_id' => 'required|exists:infraestructuras_tiendas,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
            'telefono_referencia' => 'nullable|string|max:30',
            'marca_id' => 'nullable|exists:marcas,id',
        ]);

        $tienda = $cliente->tiendas()->findOrFail($request->tienda_id);
        
        $tienda->nombre = $request->nombre;
        $tienda->descripcion = $request->descripcion;
        $tienda->telefono_referencia = $request->telefono_referencia;
        
        if ($request->filled('marca_id')) {
            $tienda->marcas()->sync([$request->marca_id]);
        } else {
            $tienda->marcas()->detach();
        }

        $tienda->save();

        return redirect()->route('cliente.personalizar', ['tienda_id' => $tienda->id])
            ->with('success', 'Información de la tienda y del modal actualizada correctamente.');
    }

    public function actualizarVitrina(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $request->validate([
            'tienda_id' => 'required|exists:infraestructuras_tiendas,id',
            'slot' => 'required|in:1,2,3',
            'imagen' => 'required|image|max:5120',
        ]);

        $tienda = $cliente->tiendas()->findOrFail($request->tienda_id);
        $slotField = 'vitrina_' . $request->slot;

        if ($tienda->$slotField) {
            Storage::disk('public')->delete($tienda->$slotField);
        }

        $path = $request->file('imagen')->store('vitrinas', 'public');
        $tienda->$slotField = $path;
        $tienda->save();

        return redirect()->route('cliente.personalizar', ['tienda_id' => $tienda->id])
            ->with('success', 'Imagen del escaparate actualizada correctamente.');
    }

    public function actualizarProductoVitrina(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $tiendaIds = $cliente->tiendas->pluck('id')->toArray();

        $request->validate([
            'producto_id' => 'nullable|exists:productos,id',
            'tienda_id' => 'required|in:' . implode(',', $tiendaIds),
            'nombre' => 'required|string|max:80',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'categoria_id' => 'nullable|exists:categorias,id',
            'subcategoria_id' => 'nullable|exists:categorias,id',
            'marca_id' => 'nullable|exists:marcas,id',
            'imagen' => 'nullable|image|max:5120',
        ]);

        $tienda = $cliente->tiendas()->findOrFail($request->tienda_id);

        if ($request->producto_id) {
            $producto = Productos::whereIn('infraestructuras_tienda_id', $tiendaIds)->findOrFail($request->producto_id);
            $producto->update([
                'nombre' => $request->nombre,
                'precio' => $request->precio,
                'descripcion' => $request->descripcion,
            ]);

            if ($request->hasFile('imagen')) {
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
            
            $msg = 'Producto actualizado correctamente.';
        } else {
            $request->validate([
                'categoria_id' => 'required',
                'subcategoria_id' => 'required',
                'marca_id' => 'required',
                'imagen' => 'required|image|max:5120',
            ]);

            $producto = Productos::create([
                'nombre' => $request->nombre,
                'precio' => $request->precio,
                'descripcion' => $request->descripcion,
                'infraestructuras_tienda_id' => $tienda->id,
                'categoria_id' => $request->subcategoria_id,
                'marca_id' => $request->marca_id,
                'estado' => 'activo',
            ]);

            $path = $request->file('imagen')->store('productos', 'public');
            ProductosImagenes::create([
                'producto_id' => $producto->id,
                'url' => $path,
                'tipo' => 'principal',
            ]);

            $msg = 'Nuevo producto creado correctamente.';
        }

        return redirect()->route('cliente.personalizar', ['tienda_id' => $tienda->id])
            ->with('success', $msg);
    }

    public function actualizarMarcaLogoRapido(Request $request)
    {
        $cliente = $this->getClienteOrAbort();
        $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'logo' => 'required|image|max:2048',
        ]);

        $marca = Marcas::where('cliente_id', $cliente->id)->findOrFail($request->marca_id);

        if ($request->hasFile('logo')) {
            if ($marca->logo) {
                Storage::disk('public')->delete($marca->logo);
            }
            $path = $request->file('logo')->store('marcas-logos', 'public');
            $marca->logo = $path;
            $marca->save();
        }

        return back()->with('success', 'Logotipo de la marca actualizado correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CATEGORÍAS
    // ─────────────────────────────────────────────────────────────────────────

    public function categorias()
    {
        $this->getClienteOrAbort();

        // Categorías raíz con sus subcategorías
        $categorias = Categorias::whereNull('categoria_padre_id')
            ->with('subcategorias')
            ->orderBy('nombre')
            ->get();

        return view('cliente.categorias.index', compact('categorias'));
    }

    public function storeCategoria(Request $request)
    {
        $this->getClienteOrAbort();

        $request->validate([
            'nombre'             => 'required|string|max:100',
            'categoria_padre_id' => 'nullable|exists:categorias,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        Categorias::create([
            'nombre'             => $request->nombre,
            'descripcion'        => $request->descripcion,
            'categoria_padre_id' => $request->categoria_padre_id ?: null,
            'estado'             => 'activo',
            'tipo'               => 'categoria',
        ]);

        $tipo = $request->categoria_padre_id ? 'Subcategoría' : 'Categoría';
        return back()->with('success', "{$tipo} creada correctamente.");
    }

    public function destroyCategoria($id)
    {
        $this->getClienteOrAbort();

        $categoria = Categorias::withCount(['subcategorias', 'productos'])->findOrFail($id);

        if ($categoria->subcategorias_count > 0) {
            return back()->with('error', 'No puedes eliminar una categoría que tiene subcategorías. Elimínalas primero.');
        }

        if ($categoria->productos_count > 0) {
            return back()->with('error', 'No puedes eliminar esta categoría porque tiene productos asociados.');
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada correctamente.');
    }
}

