<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable motivo_rechazo to suscripciones_pagos
        Schema::table('suscripciones_pagos', function (Blueprint $table) {
            $table->text('motivo_rechazo')
                ->nullable()
                ->after('observaciones');
        });

        // 2. Create payment_settings table
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('banco_nombre')->nullable();
            $table->string('banco_nro_cuenta')->nullable();
            $table->string('banco_titular')->nullable();
            $table->string('qr_imagen')->nullable();
            $table->timestamps();
        });

        // 3. Create client_notifications table
        Schema::create('client_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('cascade');
            $table->string('tipo')->default('info');
            $table->string('titulo');
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->timestamps();
        });

        // 4. Update check constraint on suscripciones_cobros
        DB::statement('ALTER TABLE suscripciones_cobros DROP CONSTRAINT IF EXISTS suscripciones_cobros_estado_check');
        DB::statement("ALTER TABLE suscripciones_cobros ADD CONSTRAINT suscripciones_cobros_estado_check CHECK (estado IN ('pendiente','parcial','pagado','vencido','anulado','pendiente_confirmacion'))");

        // 5. Seed default settings record
        DB::table('payment_settings')->insert([
            'banco_nombre' => 'Banco Nacional de Bolivia',
            'banco_nro_cuenta' => '1234-5678-90',
            'banco_titular' => 'Administración Mall Gran Vía',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Revert check constraint
        DB::statement('ALTER TABLE suscripciones_cobros DROP CONSTRAINT IF EXISTS suscripciones_cobros_estado_check');
        DB::statement("ALTER TABLE suscripciones_cobros ADD CONSTRAINT suscripciones_cobros_estado_check CHECK (estado IN ('pendiente','parcial','pagado','vencido','anulado'))");

        Schema::dropIfExists('client_notifications');
        Schema::dropIfExists('payment_settings');

        Schema::table('suscripciones_pagos', function (Blueprint $table) {
            $table->dropColumn('motivo_rechazo');
        });
    }
};
