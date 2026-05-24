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
        Schema::table('suscripciones_pagos', function (Blueprint $table) {
            $table->decimal('pago_pendiente', 10, 2)->nullable()->after('monto_pagado');
            $table->string('estado_snapshot')->nullable()->after('pago_pendiente');
        });

        Schema::table('suscripciones_cobros', function (Blueprint $table) {
            $table->decimal('saldo_pendiente', 10, 2)->nullable()->after('monto');
            $table->string('estado_snapshot')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suscripciones_pagos', function (Blueprint $table) {
            $table->dropColumn(['pago_pendiente', 'estado_snapshot']);
        });

        Schema::table('suscripciones_cobros', function (Blueprint $table) {
            $table->dropColumn(['saldo_pendiente', 'estado_snapshot']);
        });
    }
};
