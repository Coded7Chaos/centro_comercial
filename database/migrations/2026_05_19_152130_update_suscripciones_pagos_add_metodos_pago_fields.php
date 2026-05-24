<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripciones_pagos', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | GENERALES
            |--------------------------------------------------------------------------
            */

            $table->decimal('monto_total', 10, 2)
                ->nullable()
                ->after('monto_pagado');

            $table->time('hora_pago')
                ->nullable()
                ->after('fecha_pago');

            $table->timestamp('fecha_hora_operacion')
                ->nullable()
                ->after('hora_pago');

            $table->string('estado_verificacion')
                ->default('pendiente')
                ->after('metodo_pago');

            /*
            |--------------------------------------------------------------------------
            | EFECTIVO
            |--------------------------------------------------------------------------
            */

            $table->string('nombre_pagador')
                ->nullable()
                ->after('estado_verificacion');

            /*
            |--------------------------------------------------------------------------
            | TRANSFERENCIA
            |--------------------------------------------------------------------------
            */

            $table->string('numero_transaccion')
                ->nullable()
                ->after('nombre_pagador');

            $table->string('banco_origen')
                ->nullable()
                ->after('numero_transaccion');

            $table->string('banco_otro')
                ->nullable()
                ->after('banco_origen');

            $table->string('titular_transferencia')
                ->nullable()
                ->after('banco_otro');

            /*
            |--------------------------------------------------------------------------
            | QR
            |--------------------------------------------------------------------------
            */

            $table->string('codigo_qr')
                ->nullable()
                ->after('titular_transferencia');

            $table->string('billetera_qr')
                ->nullable()
                ->after('codigo_qr');

            $table->string('billetera_qr_otro')
                ->nullable()
                ->after('billetera_qr');

            /*
            |--------------------------------------------------------------------------
            | TARJETA
            |--------------------------------------------------------------------------
            */

            $table->string('codigo_autorizacion')
                ->nullable()
                ->after('billetera_qr_otro');

            $table->string('ultimos_4_tarjeta', 4)
                ->nullable()
                ->after('codigo_autorizacion');

            $table->string('marca_tarjeta')
                ->nullable()
                ->after('ultimos_4_tarjeta');

            $table->string('marca_tarjeta_otro')
                ->nullable()
                ->after('marca_tarjeta');
        });
    }

    public function down(): void
    {
        Schema::table('suscripciones_pagos', function (Blueprint $table) {

            $table->dropColumn([
                'monto_total',
                'hora_pago',
                'fecha_hora_operacion',
                'estado_verificacion',

                'nombre_pagador',

                'numero_transaccion',
                'banco_origen',
                'banco_otro',
                'titular_transferencia',

                'codigo_qr',
                'billetera_qr',
                'billetera_qr_otro',

                'codigo_autorizacion',
                'ultimos_4_tarjeta',
                'marca_tarjeta',
                'marca_tarjeta_otro',
            ]);
        });
    }
};