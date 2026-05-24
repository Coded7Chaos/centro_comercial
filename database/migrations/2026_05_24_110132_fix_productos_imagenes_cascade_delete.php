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
              Schema::table('productos_imagenes', function (Blueprint $table) {
          // Primero quitamos la FK vieja
          $table->dropForeign(['producto_id']);

          // La volvemos a crear con cascadeOnDelete
          $table->foreign('producto_id')
              ->references('id')
              ->on('productos')
              ->cascadeOnDelete();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
