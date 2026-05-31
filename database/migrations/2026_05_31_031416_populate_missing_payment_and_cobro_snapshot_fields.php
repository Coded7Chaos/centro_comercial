<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void
     {
         $cobros = \Illuminate\Support\Facades\DB::table('suscripciones_cobros')->get();
         foreach ($cobros as $cobro) {
             $pagos = \Illuminate\Support\Facades\DB::table('suscripciones_pagos')
                 ->where('suscripcion_cobro_id', $cobro->id)
                 ->orderBy('id', 'asc')
                 ->get();
 
             $totalPagado = 0;
             foreach ($pagos as $pago) {
                 $totalPagado += (float) $pago->monto_pagado;
                 $pending = max(0.0, (float)$cobro->monto - $totalPagado);
                 $estadoSnap = ($totalPagado >= (float)$cobro->monto) ? 'pagado' : 'parcial';
 
                 \Illuminate\Support\Facades\DB::table('suscripciones_pagos')
                     ->where('id', $pago->id)
                     ->update([
                         'pago_pendiente' => $pago->pago_pendiente ?? $pending,
                         'estado_snapshot' => $pago->estado_snapshot ?? $estadoSnap,
                     ]);
             }
 
             // Update cobro itself if they are null
             $saldoPendiente = max(0.0, (float)$cobro->monto - $totalPagado);
             $estadoCobro = $cobro->estado;
             \Illuminate\Support\Facades\DB::table('suscripciones_cobros')
                 ->where('id', $cobro->id)
                 ->update([
                     'saldo_pendiente' => $cobro->saldo_pendiente ?? $saldoPendiente,
                     'estado_snapshot' => $cobro->estado_snapshot ?? $estadoCobro,
                 ]);
         }
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         // No action needed for rollback as this just populates values
     }
};
