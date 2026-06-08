<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->string('email_contacto')->nullable()->after('telefono_referencia');
        });
    }

    public function down(): void
    {
        Schema::table('infraestructuras_tiendas', function (Blueprint $table) {
            $table->dropColumn('email_contacto');
        });
    }
};
