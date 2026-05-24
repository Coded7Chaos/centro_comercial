<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>
        Movimiento de Suscripción
    </title>

    <style>

        *{
            box-sizing: border-box;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #1f2937;
            background: #f3f4f6;
        }

        .container{
            padding: 30px;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .header{
            background: #111827;
            color: white;
            padding: 25px;
            border-radius: 14px;
            margin-bottom: 25px;
        }

        .header h1{
            margin: 0;
            font-size: 28px;
        }

        .header p{
            margin-top: 6px;
            color: #d1d5db;
        }

        /*
        |--------------------------------------------------------------------------
        | CARDS
        |--------------------------------------------------------------------------
        */

        .card{
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-title{
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }

        .info{
            margin-bottom: 10px;
        }

        .label{
            font-weight: bold;
            color: #374151;
        }

        /*
        |--------------------------------------------------------------------------
        | BADGES
        |--------------------------------------------------------------------------
        */

        .badge{
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .badge-cobro{
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-pago{
            background: #dcfce7;
            color: #166534;
        }

        .badge-tipo{
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            color: white;
            font-size: 11px;
            font-weight: bold;
        }

        .mensual{
            background: #2563eb;
        }

        .bimestral{
            background: #7c3aed;
        }

        .trimestral{
            background: #9333ea;
        }

        .semestral{
            background: #db2777;
        }

        .anual{
            background: #16a34a;
        }

        .semanal{
            background: #ea580c;
        }

        .personalizado{
            background: #ca8a04;
        }

        /*
        |--------------------------------------------------------------------------
        | TIMELINE
        |--------------------------------------------------------------------------
        */

        .timeline{
            position: relative;
            margin-top: 10px;
            padding-left: 30px;
        }

        .timeline::before{
            content: '';
            position: absolute;
            top: 0;
            left: 10px;
            width: 3px;
            height: 100%;
            background: #d1d5db;
        }

        .event{
            position: relative;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 18px;
            margin-bottom: 18px;
        }

        .event::before{
            content: '';
            position: absolute;
            left: -26px;
            top: 22px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
        }

        .event-cobro::before{
            background: #dc2626;
        }

        .event-pago::before{
            background: #16a34a;
        }

        /*
        |--------------------------------------------------------------------------
        | FECHAS
        |--------------------------------------------------------------------------
        */

        .fecha{
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 10px;
        }

        /*
        |--------------------------------------------------------------------------
        | MONTO
        |--------------------------------------------------------------------------
        */

        .monto{
            margin-top: 12px;
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer{
            margin-top: 35px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }

    </style>

</head>

<body>

<div class="container">

    {{-- HEADER --}}
    <div class="header">

        <h1>
            MOVIMIENTO DE SUSCRIPCIÓN
        </h1>

        <p>
            Historial cronológico de cobros y pagos
        </p>

    </div>

    {{-- INFORMACIÓN GENERAL --}}
    <div class="card">

        <div class="card-title">
            Información General
        </div>

        <div class="info">

            <span class="label">
                Cliente:
            </span>

            #{{ $cliente?->id }}
            -
            {{ $cliente?->user?->nombres }}
            {{ $cliente?->user?->apellido_paterno }}
            {{ $cliente?->user?->apellido_materno }}

        </div>

        <div class="info">

            <span class="label">
                Infraestructura:
            </span>

            {{ $infraestructura?->nombre ?? '---' }}

        </div>

        <div class="info">

            <span class="label">
                Piso:
            </span>

            {{ $piso?->nombre ?? '---' }}

        </div>

        <div class="info">

            <span class="label">
                Tienda:
            </span>

            #{{ $tienda?->numero ?? '---' }}

            @if($tienda?->nombre)

                - {{ $tienda->nombre }}

            @endif

        </div>

        <div class="info">

            <span class="label">
                Marca principal:
            </span>

            {{ $marca?->nombre ?? '---' }}

        </div>

        <div class="info">

            <span class="label">
                Tamaño:
            </span>

            {{ ucfirst($tienda?->tamano ?? '---') }}

        </div>

        <div class="info">

            <span class="label">
                Tipo de suscripción:
            </span>

            <span class="badge-tipo {{ $suscripcion->tipo }}">

                {{ ucfirst($suscripcion->tipo) }}

            </span>

        </div>

        <div class="info">

            <span class="label">
                Precio:
            </span>

            Bs {{ number_format($suscripcion->precio, 2) }}

        </div>

        <div class="info">

            <span class="label">
                Fecha inicio:
            </span>

            {{ \Carbon\Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y') }}

        </div>

        <div class="info">

            <span class="label">
                Fecha fin:
            </span>

            {{ \Carbon\Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') }}

        </div>

    </div>

    {{-- TIMELINE --}}
    <div class="timeline">

        @foreach($movimientos as $m)

            <div class="event {{ $m['tipo'] == 'COBRO' ? 'event-cobro' : 'event-pago' }}">

                {{-- BADGE --}}
                <div class="badge {{ $m['tipo'] == 'COBRO' ? 'badge-cobro' : 'badge-pago' }}">

                    {{ $m['tipo'] }}

                </div>

                {{-- FECHA --}}
                <div class="fecha">

                    {{ \Carbon\Carbon::parse($m['fecha'])->format('d/m/Y H:i') }}

                </div>

                {{-- DETALLE --}}
                <div>

                    {{ $m['detalle'] }}

                </div>

                {{-- MONTO --}}
                <div class="monto">

                    Bs {{ number_format($m['monto'], 2) }}

                </div>

            </div>

        @endforeach

    </div>

    {{-- FOOTER --}}
    <div class="footer">

        Sistema de gestión comercial •
        Documento generado el
        {{ now()->format('d/m/Y H:i') }}

    </div>

</div>

</body>
</html>