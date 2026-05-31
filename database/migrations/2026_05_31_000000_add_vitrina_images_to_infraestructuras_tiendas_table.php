<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->string('vitrina_1')->nullable();
            $table->string('vitrina_2')->nullable();
            $table->string('vitrina_3')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->dropColumn(['vitrina_1', 'vitrina_2', 'vitrina_3']);
        });
    }
};
