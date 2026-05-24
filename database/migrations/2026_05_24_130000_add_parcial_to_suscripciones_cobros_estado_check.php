<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE suscripciones_cobros DROP CONSTRAINT IF EXISTS suscripciones_cobros_estado_check');
        DB::statement("ALTER TABLE suscripciones_cobros ADD CONSTRAINT suscripciones_cobros_estado_check CHECK (estado IN ('pendiente','parcial','pagado','vencido','anulado'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE suscripciones_cobros DROP CONSTRAINT IF EXISTS suscripciones_cobros_estado_check');
        DB::statement("ALTER TABLE suscripciones_cobros ADD CONSTRAINT suscripciones_cobros_estado_check CHECK (estado IN ('pendiente','pagado','vencido','anulado'))");
    }
};
