<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Suscripciones;
use App\Models\SuscripcionesPagos;
use App\Support\ActiveInfraestructura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class ReporteAuditoriaFinancieraPdfController extends Controller
{
    public function descargar()
    {
        abort_unless(auth()->user()?->can('View:BalanceSuscripciones'), 403);

        $suscripciones = ActiveInfraestructura::scopeQuery(
            Suscripciones::query()->with(['cliente.user', 'cobros.pagos', 'infraestructurasTienda.piso.infraestructura']),
            'infraestructurasTienda.piso'
        )
            ->orderBy('cliente_id')
            ->orderBy('infraestructuras_tienda_id')
            ->orderBy('fecha_inicio')
            ->get();

        $rows = $suscripciones
            ->groupBy(fn (Suscripciones $suscripcion) => $suscripcion->cliente_id . ':' . ($suscripcion->infraestructuras_tienda_id ?? 'null'))
            ->map(function ($grupo) {
                /** @var \App\Models\Suscripciones $record */
                $record = $grupo->first();
                $user = $record->cliente?->user;
                $suscripcionIds = $grupo->pluck('id');
                $cobros = $grupo->flatMap->cobros;
                $deuda = (float) $cobros->sum('monto');
                $pagado = (float) SuscripcionesPagos::where('estado_verificacion', 'verificado')
                    ->whereIn('suscripcion_cobro_id', $cobros->pluck('id'))
                    ->sum('monto_pagado');
                $saldo = max(0, $deuda - $pagado);

                return [
                    'cliente' => $user
                        ? trim($user->nombres . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno)
                        : 'Sin cliente',
                    'local' => $record->infraestructurasTienda?->numero ?? '—',
                    'tienda' => $record->infraestructurasTienda?->nombre ?: 'Sin nombre comercial',
                    'contratos' => $suscripcionIds->count(),
                    'fecha_inicio' => $grupo->min('fecha_inicio')
                        ? Carbon::parse($grupo->min('fecha_inicio'))->format('d/m/Y')
                        : '—',
                    'fecha_fin' => $grupo->max('fecha_fin')
                        ? Carbon::parse($grupo->max('fecha_fin'))->format('d/m/Y')
                        : '—',
                    'deuda' => $deuda,
                    'pagado' => $pagado,
                    'saldo' => $saldo,
                    'porcentaje_pagado' => $deuda > 0 ? round(($pagado / $deuda) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('saldo')
            ->values();

        $totalDeuda = (float) $rows->sum('deuda');
        $totalPagado = (float) $rows->sum('pagado');
        $totalSaldo = (float) $rows->sum('saldo');
        $maxDeuda = max(1, (float) $rows->max('deuda'));

        $pdf = Pdf::loadView('pdf.reporte-auditoria-financiera', [
            'rows' => $rows,
            'totalContratos' => $suscripciones->count(),
            'totalClientesLocales' => $rows->count(),
            'totalDeuda' => $totalDeuda,
            'totalPagado' => $totalPagado,
            'totalSaldo' => $totalSaldo,
            'porcentajePagado' => $totalDeuda > 0 ? round(($totalPagado / $totalDeuda) * 100, 1) : 0,
            'maxDeuda' => $maxDeuda,
            'infraestructura' => ActiveInfraestructura::get()?->nombre,
            'generadoEn' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('auditoria-financiera-' . now()->format('Y-m-d') . '.pdf');
    }
}
