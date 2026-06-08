<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\SuscripcionesCobros;
use App\Support\ActiveInfraestructura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class ReporteMorosidadPdfController extends Controller
{
    public function descargar()
    {
        abort_unless(auth()->user()?->can('View:ReporteMorosidad'), 403);

        $cobros = ActiveInfraestructura::scopeQuery(
            SuscripcionesCobros::query()
                ->with(['pagos', 'suscripcion.cliente.user', 'suscripcion.infraestructurasTienda.piso.infraestructura'])
                ->whereIn('estado', ['pendiente', 'parcial', 'vencido'])
                ->whereDate('fecha_vencimiento', '<', now()->toDateString()),
            'suscripcion.infraestructurasTienda.piso'
        )
            ->orderBy('fecha_vencimiento')
            ->orderBy('id')
            ->get();

        $rows = $cobros->map(function (SuscripcionesCobros $cobro): array {
            $user = $cobro->suscripcion?->cliente?->user;
            $pagado = (float) $cobro->pagos
                ->where('estado_verificacion', 'verificado')
                ->sum('monto_pagado');
            $saldo = max(0, (float) $cobro->monto - $pagado);

            return [
                'cliente' => $user
                    ? trim($user->nombres . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno)
                    : 'Sin cliente',
                'concepto' => $cobro->concepto,
                'local' => $cobro->suscripcion?->infraestructurasTienda?->nombre
                    ?: ('Local ' . ($cobro->suscripcion?->infraestructurasTienda?->numero ?? '—')),
                'monto' => (float) $cobro->monto,
                'pagado' => $pagado,
                'saldo' => $saldo,
                'fecha_vencimiento' => Carbon::parse($cobro->fecha_vencimiento)->format('d/m/Y'),
                'dias_atraso' => Carbon::parse($cobro->fecha_vencimiento)
                    ->startOfDay()
                    ->diffInDays(Carbon::now()->startOfDay()),
                'estado' => ucfirst(str_replace('_', ' ', $cobro->estado)),
            ];
        });

        $totalMonto = (float) $rows->sum('monto');
        $totalPagado = (float) $rows->sum('pagado');
        $totalSaldo = (float) $rows->sum('saldo');

        $pdf = Pdf::loadView('pdf.reporte-morosidad', [
            'rows' => $rows,
            'totalCobros' => $rows->count(),
            'totalClientes' => $rows->pluck('cliente')->unique()->count(),
            'totalMonto' => $totalMonto,
            'totalPagado' => $totalPagado,
            'totalSaldo' => $totalSaldo,
            'infraestructura' => ActiveInfraestructura::get()?->nombre,
            'generadoEn' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte-morosidad-' . now()->format('Y-m-d') . '.pdf');
    }
}
