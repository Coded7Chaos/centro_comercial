<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Suscripciones;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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

        $cliente         = $suscripcion->cliente;
        $tienda          = $suscripcion->infraestructurasTienda;
        $piso            = $tienda?->piso;
        $infraestructura = $piso?->infraestructura;

        // Calcular pago mensual y garantía
        $totalMeses  = max(1, (int) round(
            Carbon::parse($suscripcion->fecha_inicio)
                ->diffInMonths(Carbon::parse($suscripcion->fecha_fin)->addDay())
        ));
        $pagoMensual = (float) $suscripcion->precio / $totalMeses;
        $garantia    = $pagoMensual;
        $precioTotal = (float) $suscripcion->precio + $garantia;

        // ── COBROS PENDIENTES (pendiente / vencido / parcial) ──────────
        $cobrosPendientes = $suscripcion->cobros
            ->whereIn('estado', ['pendiente', 'vencido', 'parcial'])
            ->sortBy('fecha_vencimiento');

        // ── PAGOS REALIZADOS (todos los pagos de todos los cobros) ─────
        $pagosRealizados = $suscripcion->cobros
            ->flatMap(function ($cobro) {
                return $cobro->pagos->map(fn ($pago) => [
                    'cobro_concepto' => $cobro->concepto,
                    'pago'           => $pago,
                ]);
            })
            ->sortBy(fn ($item) => $item['pago']->fecha_pago);

        // ── ARMAR MOVIMIENTOS ──────────────────────────────────────────
        $movimientos = [];

        foreach ($cobrosPendientes as $cobro) {
            $movimientos[] = [
                'tipo'    => 'COBRO',
                'fecha'   => $cobro->fecha_vencimiento,
                'id'      => $cobro->id,
                'detalle' => $cobro->concepto,
                'monto'   => $cobro->monto,
                'estado'  => $cobro->estado,
                'metodo'  => null,
            ];
        }

        foreach ($pagosRealizados as $item) {
            $pago = $item['pago'];
            $movimientos[] = [
                'tipo'    => 'PAGO',
                'fecha'   => $pago->fecha_pago,
                'id'      => $pago->id,
                'detalle' => $item['cobro_concepto'],
                'monto'   => $pago->monto_pagado,
                'estado'  => 'pagado',
                'metodo'  => $pago->metodo_pago ? ucfirst($pago->metodo_pago) : null,
            ];
        }

        // Ordenar: cobros por fecha_vencimiento, pagos por fecha_pago.
        // Cuando dos movimientos tienen la misma fecha, el de menor id (creado antes) va primero.
        usort($movimientos, function ($a, $b) {
            $diff = strtotime($a['fecha']) <=> strtotime($b['fecha']);
            if ($diff !== 0) return $diff;
            return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
        });

        $pdf = Pdf::loadView('pdf.reporte-suscripcion-movimiento', [
            'suscripcion'    => $suscripcion,
            'cliente'        => $cliente,
            'tienda'         => $tienda,
            'piso'           => $piso,
            'infraestructura' => $infraestructura,
            'movimientos'    => $movimientos,
            'pago_mensual'   => $pagoMensual,
            'garantia'       => $garantia,
            'precio_total'   => $precioTotal,
            'total_meses'    => $totalMeses,
        ]);

        return $pdf->stream("suscripcion-movimiento-{$suscripcion->id}.pdf");
    }
}
