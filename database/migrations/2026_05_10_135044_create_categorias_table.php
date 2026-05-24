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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo')->default('categoria');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->foreignId('categoria_padre_id')->nullable()->constrained('categorias')->onDelete('cascade');
            $table->timestamps();
        });

        if (config('database.default') === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE categorias
                ADD CONSTRAINT categorias_tipo_check
                CHECK (tipo IN ('categoria', 'subcategoria'))
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
