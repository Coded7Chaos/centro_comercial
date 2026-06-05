<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\SuscripcionesCobros;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteCobrosController extends Controller
{
    public function cobro($id)
    {
        $cobro = SuscripcionesCobros::with([
            'suscripcion.cliente.user',
            'suscripcion.marca',
            'suscripcion.infraestructurasTienda.piso.infraestructura',
        ])->findOrFail($id);

        $suscripcion = $cobro->suscripcion;
        $cliente = $suscripcion?->cliente;
        $marca = $suscripcion?->marca;
        $tienda = $suscripcion?->infraestructurasTienda;
        $piso = $tienda?->piso;
        $infraestructura = $piso?->infraestructura;

        // Historial: todos los pagos verificados de cobros de esta suscripción
        // cuya fecha_vencimiento sea ANTERIOR a la del cobro actual.
        // Cobro #1 → ningún cobro previo → historial vacío.
        // Cobro #6 → muestra pagos de cobros #1..#5.
        $pagos = \App\Models\SuscripcionesPagos::where('estado_verificacion', 'verificado')
            ->whereHas('cobro', fn ($q) => $q
                ->where('suscripcion_id', $suscripcion->id)
                ->where('fecha_vencimiento', '<', $cobro->fecha_vencimiento)
            )
            ->orderBy('fecha_pago', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdf.reporte-cobro', [
            'cobro' => $cobro,
            'suscripcion' => $suscripcion,
            'cliente' => $cliente,
            'marca' => $marca,
            'tienda' => $tienda,
            'piso' => $piso,
            'infraestructura' => $infraestructura,
            'pagos' => $pagos,
        ]);

        return $pdf->stream("cobro-{$cobro->id}.pdf");
    }
}
