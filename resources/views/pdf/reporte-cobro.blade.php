<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>
        Reporte de Cobro
    </title>

    <style>

        *{
            box-sizing: border-box;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }

        .container{
            padding: 30px;
        }

        /* HEADER */

        .header{
            background: #111827;
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .header h1{
            margin: 0;
            font-size: 28px;
        }

        .header p{
            margin-top: 5px;
            color: #d1d5db;
        }

        /* GRID */

        .row{
            width: 100%;
            margin-bottom: 20px;
        }

        .col{
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        .col-right{
            margin-left: 3%;
        }

        /* CARDS */

        .card{
            background: white;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 18px;
            border: 1px solid #e5e7eb;
        }

        .card-title{
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }

        .info{
            margin-bottom: 6px;
        }

        .label{
            font-weight: bold;
            color: #374151;
        }

        /* BADGES */

        .badge{
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-success{
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger{
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning{
            background: #fef3c7;
            color: #92400e;
        }

        /* TABLE */

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead{
            background: #111827;
            color: white;
        }

        th{
            padding: 12px;
            font-size: 11px;
            text-align: left;
        }

        td{
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:nth-child(even){
            background: #f9fafb;
        }

        /* TOTAL */

        .total-box{
            margin-top: 25px;
            background: #111827;
            color: white;
            padding: 18px;
            border-radius: 12px;
            text-align: right;
        }

        .total-box h2{
            margin: 0;
            font-size: 24px;
        }

        /* FOOTER */

        .footer{
            margin-top: 35px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }

    </style>
</head>

<body>

<div class="container">

    {{-- HEADER --}}
    <div class="header">

        <h1>
            REPORTE DE COBRO
        </h1>

        <p>
            Documento generado automáticamente
        </p>

    </div>

    {{-- INFO PRINCIPAL --}}
    <div class="row">

        {{-- COBRO --}}
        <div class="col">

            <div class="card">

                <div class="card-title">
                    Información del Cobro
                </div>

                <div class="info">
                    <span class="label">Código:</span>
                    #{{ $cobro->id }}
                </div>

                <div class="info">
                    <span class="label">Concepto:</span>
                    {{ $cobro->concepto ?? 'Sin concepto' }}
                </div>

                <div class="info">
                    <span class="label">Fecha vencimiento:</span>
                    {{ $cobro->fecha_vencimiento }}
                </div>

                <div class="info">
                    <span class="label">Estado:</span>

                    @if($cobro->estado == 'pagado')

                        <span class="badge badge-success">
                            PAGADO
                        </span>

                    @elseif($cobro->estado == 'pendiente')

                        <span class="badge badge-warning">
                            PENDIENTE
                        </span>

                    @else

                        <span class="badge badge-danger">
                            VENCIDO
                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- CLIENTE --}}
        <div class="col col-right">

            <div class="card">

                <div class="card-title">
                    Información del Cliente
                </div>

                <div class="info">
                    <span class="label">Cliente:</span>
                    {{ $cliente?->user?->nombres }}
                    {{ $cliente?->user?->apellido_paterno }}
                    {{ $cliente?->user?->apellido_materno }}
                </div>

                <div class="info">
                    <span class="label">CI:</span>
                    {{ $cliente?->ci ?? '---' }}
                </div>

                <div class="info">
                    <span class="label">Celular:</span>
                    {{ $cliente?->numero_celular ?? '---' }}
                </div>

                <div class="info">
                    <span class="label">Marca:</span>
                    {{ $marca?->nombre ?? '---' }}
                </div>

            </div>

        </div>

    </div>

    {{-- UBICACIÓN --}}
    <div class="card">

        <div class="card-title">
            Ubicación Comercial
        </div>

        <div class="info">
            <span class="label">Infraestructura:</span>
            {{ $infraestructura?->nombre ?? '---' }}
        </div>

        <div class="info">
            <span class="label">Piso:</span>
            {{ $piso?->nombre ?? '---' }}
        </div>

        <div class="info">
            <span class="label">Tienda:</span>
            #{{ $tienda?->numero ?? '---' }}
        </div>

    </div>

    {{-- PAGOS --}}
    <div class="card">

        <div class="card-title">
            Historial de Pagos
        </div>

        @if(count($pagos) > 0)

            <table>

                <thead>

                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Referencia</th>
                    </tr>

                </thead>

                <tbody>

                @foreach($pagos as $pago)

                    <tr>

                        <td>
                            {{ $pago->fecha_pago }}
                        </td>

                        <td>
                            Bs {{ number_format($pago->monto_pagado, 2) }}
                        </td>

                        <td>
                            {{ ucfirst($pago->metodo_pago) }}
                        </td>

                        <td>
                            {{ $pago->referencia ?? '---' }}
                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        @else

            <p>
                No existen pagos registrados.
            </p>

        @endif

    </div>

    {{-- TOTAL --}}
    <div class="total-box">

        <div>
            TOTAL DEL COBRO
        </div>

        <h2>
            Bs {{ number_format($cobro->monto ?? 0, 2) }}
        </h2>

    </div>

    {{-- FOOTER --}}
    <div class="footer">

        Sistema de gestión comercial •
        Generado el {{ now()->format('d/m/Y H:i') }}

    </div>

</div>

</body>
</html>