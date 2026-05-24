<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {

            $table->foreignId('infraestructuras_tienda_id')
                ->nullable()
                ->constrained('infraestructuras_tiendas')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {

            $table->dropForeign(['infraestructuras_tienda_id']);

            $table->dropColumn('infraestructuras_tienda_id');
        });
    }
};