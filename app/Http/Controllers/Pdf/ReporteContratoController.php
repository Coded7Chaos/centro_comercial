<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Suscripciones;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteContratoController extends Controller
{
    public function descargar($id)
    {
        $suscripcion = Suscripciones::with([
            'cliente.user',
            'marca',
            'infraestructurasTienda.piso.infraestructura',
        ])->findOrFail($id);

        $cliente = $suscripcion->cliente;
        $tienda = $suscripcion->infraestructurasTienda;
        $marca = $suscripcion->marca;
        $piso = $tienda?->piso;
        $infraestructura = $piso?->infraestructura;

        $pdf = Pdf::loadView('pdf.contrato', [
            'suscripcion' => $suscripcion,
            'cliente' => $cliente,
            'marca' => $marca,
            'tienda' => $tienda,
            'piso' => $piso,
            'infraestructura' => $infraestructura,
            'fechaDocumento' => now()->translatedFormat('d \d\e F \d\e Y'),
        ]);

        return $pdf->stream("contrato-alquiler-local-{$tienda?->numero}.pdf");
    }
}
