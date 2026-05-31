<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tamano_etiquetas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->decimal('desde', 8, 2);
            $table->decimal('hasta', 8, 2);
            $table->timestamps();
        });

        Schema::create('tamano_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tamano_etiqueta_id')->unique()->constrained('tamano_etiquetas')->cascadeOnDelete();
            $table->decimal('precio_mensual', 10, 2);
            $table->timestamps();
        });

        Schema::create('descuentos_tiempo', function (Blueprint $table) {
            $table->id();
            $table->integer('min_meses')->unique();
            $table->decimal('descuento', 5, 2); // e.g., 10.00 %
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descuentos_tiempo');
        Schema::dropIfExists('tamano_precios');
        Schema::dropIfExists('tamano_etiquetas');
    }
};
