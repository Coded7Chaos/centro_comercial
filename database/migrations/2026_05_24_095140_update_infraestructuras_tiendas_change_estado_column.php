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
         Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->dropColumn('estado');
            $table->foreignId('id_estado')->nullable()->constrained('estados_tiendas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infraestructuras_tiendas', function(Blueprint $table) {
            $table->dropForeign(['id_estado']);
            $table->dropColumn('id_estado');
            $table->string('estado')->default('activo');
        });
    }
};
