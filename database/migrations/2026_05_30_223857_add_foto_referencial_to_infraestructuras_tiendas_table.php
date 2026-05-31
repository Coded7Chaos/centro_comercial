<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->string('foto_referencial')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->dropColumn('foto_referencial');
        });
    }
};
