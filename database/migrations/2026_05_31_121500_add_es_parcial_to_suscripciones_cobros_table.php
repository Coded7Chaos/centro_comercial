<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suscripciones_cobros', function (Blueprint $table) {
            $table->boolean('es_parcial')->default(false)->after('estado');
        });

        // Mark existing partial charges
        DB::table('suscripciones_cobros')
            ->where('concepto', 'like', 'Saldo pendiente de:%')
            ->update(['es_parcial' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suscripciones_cobros', function (Blueprint $table) {
            $table->dropColumn('es_parcial');
        });
    }
};
