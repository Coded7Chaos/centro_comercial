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
        Schema::create('infraestructuras_pisos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('infraestructura_id')
                ->constrained('infraestructuras');

            $table->string('nombre');
            // PB, P1, P2, etc.

            $table->integer('cantidad_tiendas')
                ->default(0);

            $table->enum('estado', [
                'activo',
                'inactivo'
            ])->default('activo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infraestructuras_pisos');
    }
};
