<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripciones_tarifas', function (Blueprint $table) {
            $table->decimal('tamano_min', 8, 2)->default(0)->after('id');
            $table->decimal('tamano_max', 8, 2)->default(0)->after('tamano_min');
            $table->string('etiqueta')->nullable()->after('tamano_max');
        });

        Schema::table('suscripciones_tarifas', function (Blueprint $table) {
            if (Schema::hasColumn('suscripciones_tarifas', 'tamano')) {
                $table->dropColumn('tamano');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suscripciones_tarifas', function (Blueprint $table) {
            $table->string('tamano')->nullable();
            $table->dropColumn(['tamano_min', 'tamano_max', 'etiqueta']);
        });
    }
};
