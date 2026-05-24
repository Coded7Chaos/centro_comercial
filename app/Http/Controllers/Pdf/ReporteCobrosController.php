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
            'pagos'
        ])->findOrFail($id);

        $suscripcion = $cobro->suscripcion;
        $cliente = $suscripcion?->cliente;
        $marca = $suscripcion?->marca;
        $tienda = $suscripcion?->infraestructurasTienda;
        $piso = $tienda?->piso;
        $infraestructura = $piso?->infraestructura;

        $pdf = Pdf::loadView('pdf.reporte-cobro', [
            'cobro' => $cobro,
            'suscripcion' => $suscripcion,
            'cliente' => $cliente,
            'marca' => $marca,
            'tienda' => $tienda,
            'piso' => $piso,
            'infraestructura' => $infraestructura,
            'pagos' => $cobro->pagos ?? [],
        ]);

        return $pdf->stream("cobro-{$cobro->id}.pdf");
    }
}
