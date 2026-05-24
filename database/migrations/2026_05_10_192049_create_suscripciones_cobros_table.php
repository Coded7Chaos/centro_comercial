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
        Schema::create('suscripciones_cobros', function (Blueprint $table) {
            $table->id();
            // SUSCRIPCIÓN

            $table->foreignId('suscripcion_id')
                ->constrained('suscripciones')
                ->cascadeOnDelete();

            // INFORMACIÓN DEL COBRO

            $table->string('concepto')
                ->nullable();

            $table->decimal('monto', 10, 2)
                ->default(0);

            $table->date('fecha_vencimiento');

            $table->date('fecha_pago')
                ->nullable();

            // ESTADOS

            $table->enum('estado', [

                'pendiente',

                'pagado',

                'vencido',

                'anulado',

            ])->default('pendiente');

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
        Schema::dropIfExists('suscripciones_cobros');
    }
};
