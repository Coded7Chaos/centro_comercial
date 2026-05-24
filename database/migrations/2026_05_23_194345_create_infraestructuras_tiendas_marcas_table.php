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
        Schema::create('infraestructuras_tiendas_marcas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraestructuras_tienda_id')->constrained('infraestructuras_tiendas')->cascadeOnDelete();
            $table->foreignId('marca_id')->constrained('marcas')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->dropForeign(['marca_id']);
            $table->dropColumn('marca_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->foreignId('marca_id')->nullable()->constrained('marcas');
        });

        Schema::dropIfExists('infraestructuras_tiendas_marcas');
    }
};
