<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Suscripciones;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteSuscripcionMovimiento extends Controller
{
    public function movimiento($id)
    {
        $suscripcion = Suscripciones::with([
            'cliente.user',
            'marca',
            'infraestructurasTienda.marcas',
            'infraestructurasTienda.piso.infraestructura',
            'cobros.pagos',
        ])->findOrFail($id);

        $cliente = $suscripcion->cliente;
        $tienda  = $suscripcion->infraestructurasTienda;

        // La suscripción ya tiene su propia marca; si no, caemos a la primera marca de la tienda.
        $marca = $suscripcion->marca ?? $tienda?->marcas->first();

        $piso = $tienda?->piso;
        $infraestructura = $piso?->infraestructura;

        /*
        |--------------------------------------------------------------------------
        | TIMELINE DE MOVIMIENTOS
        |--------------------------------------------------------------------------
        */

        $movimientos = [];

        foreach ($suscripcion->cobros as $cobro) {

            $movimientos[] = [

                'tipo' => 'COBRO',

                'fecha' => $cobro->created_at,

                'detalle' =>
                    'Cobro generado: ' .
                    $cobro->concepto,

                'monto' => $cobro->monto,

                'estado' =>
                    $cobro->estado ?? 'pendiente',
            ];

            foreach ($cobro->pagos as $pago) {

                $movimientos[] = [

                    'tipo' => 'PAGO',

                    'fecha' => $pago->fecha_pago,

                    'detalle' =>
                        'Pago realizado (' .
                        ucfirst($pago->metodo_pago) .
                        ')',

                    'monto' => $pago->monto_pagado,

                    'estado' => 'pagado',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ORDENAR TIMELINE
        |--------------------------------------------------------------------------
        */

        usort(

            $movimientos,

            fn($a, $b) =>

            strtotime($a['fecha']) <=>
            strtotime($b['fecha'])

        );

        /*
        |--------------------------------------------------------------------------
        | GENERAR PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(

            'pdf.reporte-suscripcion-movimiento',

            [

                'suscripcion' => $suscripcion,

                'cliente' => $cliente,

                'marca' => $marca,

                'tienda' => $tienda,

                'piso' => $piso,

                'infraestructura' => $infraestructura,

                'movimientos' => $movimientos,
            ]

        );

        return $pdf->stream(

            "suscripcion-movimiento-{$suscripcion->id}.pdf"

        );
    }
}