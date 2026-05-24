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
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('marca_id')->nullable()->constrained('marcas');
            $table->string('tipo')->default('mensual');
            $table->decimal('precio', 10, 2)->default(0.00);
            $table->string('tamano')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->foreignId('infraestructuras_tienda_id')->nullable()->constrained('infraestructuras_tiendas');
            $table->foreignId('infraestructuras_piso_id')->nullable()->constrained('infraestructuras_pisos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
