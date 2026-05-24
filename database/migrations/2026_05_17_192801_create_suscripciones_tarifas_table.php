<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones_tarifas', function (Blueprint $table) {

            $table->id();

            $table->string('tamano');

            $table->string('tipo');

            $table->decimal('precio',10,2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'suscripciones_tarifas'
        );
    }
};