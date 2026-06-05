<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('infraestructura_id')
                ->nullable()
                ->constrained('infraestructuras')
                ->nullOnDelete()
                ->after('categoria_padre_id');
        });

        Schema::table('suscripciones_tarifas', function (Blueprint $table) {
            $table->foreignId('infraestructura_id')
                ->nullable()
                ->constrained('infraestructuras')
                ->nullOnDelete()
                ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('infraestructura_id');
        });

        Schema::table('suscripciones_tarifas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('infraestructura_id');
        });
    }
};
