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
        Schema::table('infraestructuras_pisos', function (Blueprint $table) {
            $table->string('imagen_fondo')->default('/images/backgrounds/bg_mall_white.jpg')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infraestructuras_pisos', function (Blueprint $table) {
            $table->dropColumn('imagen_fondo');
        });
    }
};
