<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\SuscripcionesPagos;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportePagosController extends Controller
{
    public function pago($id)
    {
        $pago = SuscripcionesPagos::with([
            'cobro.pagos',
            'cobro.suscripcion.cliente.user',
            'cobro.suscripcion.marca',
            'cobro.suscripcion.infraestructurasTienda.piso.infraestructura',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | RELACIONES
        |--------------------------------------------------------------------------
        */

        $cobro = $pago->cobro;

        $suscripcion = $cobro?->suscripcion;

        $cliente = $suscripcion?->cliente;

        $marca = $suscripcion?->marca;

        $tienda = $suscripcion?->infraestructurasTienda;

        $piso = $tienda?->piso;

        $infraestructura = $piso?->infraestructura;

        /*
        |--------------------------------------------------------------------------
        | HISTORIAL
        |--------------------------------------------------------------------------
        */

        $historialPagos = $cobro
            ?->pagos()
            ->orderBy('fecha_pago')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TOTALES
        |--------------------------------------------------------------------------
        */

        $montoTotal = $cobro?->monto ?? 0;

        $totalPagado = $historialPagos
            ?->sum('monto_pagado') ?? 0;

        $saldoPendiente =
            $pago->pago_pendiente ?? 0;

        /*
        |--------------------------------------------------------------------------
        | TOTAL ANTES DE ESTE PAGO
        |--------------------------------------------------------------------------
        */

        $totalAntesPago =
            $totalPagado - $pago->monto_pagado;

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'pdf.reporte-pago',
            [

                'pago' => $pago,

                'cobro' => $cobro,

                'suscripcion' => $suscripcion,

                'cliente' => $cliente,

                'marca' => $marca,

                'tienda' => $tienda,

                'piso' => $piso,

                'infraestructura' => $infraestructura,

                'historialPagos' => $historialPagos,

                'montoTotal' => $montoTotal,

                'totalPagado' => $totalPagado,

                'saldoPendiente' => $saldoPendiente,

                'totalAntesPago' => $totalAntesPago,
            ]
        );

        return $pdf->stream(
            "recibo-pago-{$pago->id}.pdf"
        );
    }
}