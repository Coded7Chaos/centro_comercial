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
        Schema::create('infraestructuras_tiendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraestructura_piso_id')->constrained('infraestructuras_pisos')->cascadeOnDelete();
            $table->string('nombre')->nullable();
            $table->string('numero');
            $table->text('descripcion')->nullable();
            $table->string('telefono_referencia')->nullable();
            $table->string('tamano')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('marca_id')->nullable()->constrained('marcas');
            $table->string('estado')->default('disponible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infraestructuras_tiendas');
    }
};
