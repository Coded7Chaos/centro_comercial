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
        Schema::create('suscripciones_pagos', function (Blueprint $table) {
            $table->id();
            // COBRO

            $table->foreignId('suscripcion_cobro_id')
                ->constrained('suscripciones_cobros')
                ->cascadeOnDelete();

            // DATOS DEL PAGO

            $table->decimal('monto_pagado', 10, 2);

            $table->date('fecha_pago');

            // MÉTODO

            $table->enum('metodo_pago', [

                'efectivo',

                'transferencia',

                'qr',

                'tarjeta',

            ])->default('efectivo');

            // REFERENCIA

            $table->string('referencia')
                ->nullable();

            // COMPROBANTE

            $table->string('comprobante')
                ->nullable();

            // OBSERVACIONES

            $table->text('observaciones')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscripciones_pagos');
    }
};
