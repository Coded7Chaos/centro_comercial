<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>
        Recibo de Pago
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
            padding: 28px;
        }

        /* HEADER */

        .header{
            background: #0f172a;
            color: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 22px;
        }

        .header-title{
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .header-subtitle{
            color: #cbd5e1;
            font-size: 13px;
        }

        .header-grid{
            margin-top: 18px;
        }

        .header-item{
            display: inline-block;
            width: 32%;
            vertical-align: top;
        }

        .header-label{
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .header-value{
            font-size: 13px;
            font-weight: bold;
        }

        /* GRID */

        .row{
            width: 100%;
            margin-bottom: 18px;
        }

        .col{
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        .col-right{
            margin-left: 3%;
        }

        /* CARD */

        .card{
            background: white;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 18px;
            border: 1px solid #e5e7eb;
        }

        .card-title{
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 14px;
            padding-bottom: 8px;
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

        /* BADGES */

        .badge{
            display: inline-block;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-success{
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning{
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger{
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info{
            background: #dbeafe;
            color: #1d4ed8;
        }

        /* TABLE */

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead{
            background: #0f172a;
            color: white;
        }

        th{
            padding: 11px;
            text-align: left;
            font-size: 11px;
        }

        td{
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }

        tbody tr:nth-child(even){
            background: #f9fafb;
        }

        /* FINANCIAL */

        .financial{
            background: #0f172a;
            color: white;
            border-radius: 14px;
            padding: 20px;
            margin-top: 22px;
        }

        .financial-row{
            margin-bottom: 10px;
        }

        .financial-total{
            font-size: 28px;
            font-weight: bold;
            margin-top: 12px;
        }

        /* FOOTER */

        .footer{
            margin-top: 28px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }

        /* METHODS */

        .method-box{
            padding: 12px;
            border-radius: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            margin-top: 10px;
        }

    </style>

</head>

<body>

<div class="container">

    {{-- HEADER --}}
    <div class="header">

        <div class="header-title">
            RECIBO DE PAGO
        </div>

        <div class="header-subtitle">
            Sistema de Gestión Comercial
        </div>

        <div class="header-grid">

            <div class="header-item">

                <div class="header-label">
                    Recibo
                </div>

                <div class="header-value">
                    #{{ $pago->id }}
                </div>

            </div>

            <div class="header-item">

                <div class="header-label">
                    Fecha
                </div>

                <div class="header-value">
                    {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                </div>

            </div>

            <div class="header-item">

                <div class="header-label">
                    Hora
                </div>

                <div class="header-value">
                    {{ $pago->hora_pago }}
                </div>

            </div>

        </div>

    </div>

    {{-- CLIENTE Y UBICACION --}}
    <div class="row">

        <div class="col">

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
                    <span class="label">Email:</span>
                    {{ $cliente?->email ?? '---' }}
                </div>

                <div class="info">
                    <span class="label">Marca:</span>
                    {{ $marca?->nombre ?? '---' }}
                </div>

            </div>

        </div>

        <div class="col col-right">

            <div class="card">

                <div class="card-title">
                    Información Comercial
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
                    -
                    {{ $tienda?->nombre ?? 'Sin nombre' }}
                </div>

                <div class="info">
                    <span class="label">Tipo:</span>
                    {{ ucfirst($suscripcion?->tipo ?? '---') }}
                </div>

                <div class="info">
                    <span class="label">Vencimiento:</span>
                    {{ $cobro?->fecha_vencimiento ?? '---' }}
                </div>

            </div>

        </div>

    </div>

    {{-- RESUMEN FINANCIERO --}}
    <div class="card">

        <div class="card-title">
            Resumen Financiero
        </div>

        <table>

            <tbody>

                <tr>
                    <td><strong>Monto total</strong></td>
                    <td>Bs {{ number_format($montoTotal, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Total pagado antes de este pago</strong></td>
                    <td>Bs {{ number_format($totalAntesPago, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Nuevo pago realizado</strong></td>
                    <td>Bs {{ number_format($pago->monto_pagado, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Total pagado acumulado</strong></td>
                    <td>Bs {{ number_format($totalPagado, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Saldo pendiente</strong></td>
                    <td>Bs {{ number_format($saldoPendiente, 2) }}</td>
                </tr>

            </tbody>

        </table>

    </div>

    {{-- METODO DE PAGO --}}
    <div class="card">

        <div class="card-title">
            Método de Pago
        </div>

        <div class="info">
            <span class="label">Método:</span>

            <span class="badge badge-info">
                {{ strtoupper($pago->metodo_pago) }}
            </span>
        </div>

        <div class="method-box">

            @if($pago->metodo_pago === 'efectivo')

                <div class="info">
                    <span class="label">Pagador:</span>
                    {{ $pago->nombre_pagador ?? '---' }}
                </div>

            @endif

            @if($pago->metodo_pago === 'transferencia')

                <div class="info">
                    <span class="label">Referencia:</span>
                    {{ $pago->referencia ?? '---' }}
                </div>

                <div class="info">
                    <span class="label">Banco:</span>
                    {{ $pago->banco_origen ?? '---' }}
                </div>

                <div class="info">
                    <span class="label">Titular:</span>
                    {{ $pago->nombre_titular ?? '---' }}
                </div>

            @endif

            @if($pago->metodo_pago === 'qr')

                <div class="info">
                    <span class="label">Folio QR:</span>
                    {{ $pago->folio_qr ?? '---' }}
                </div>

                <div class="info">
                    <span class="label">Aplicación:</span>
                    {{ $pago->billetera_origen ?? '---' }}
                </div>

            @endif

            @if($pago->metodo_pago === 'tarjeta')

                <div class="info">
                    <span class="label">Marca:</span>
                    {{ $pago->marca_tarjeta ?? '---' }}
                </div>

                <div class="info">
                    <span class="label">Últimos 4:</span>
                    **** {{ $pago->ultimos_4 ?? '----' }}
                </div>

                <div class="info">
                    <span class="label">Autorización:</span>
                    {{ $pago->codigo_autorizacion ?? '---' }}
                </div>

            @endif

        </div>

    </div>

    {{-- HISTORIAL --}}
    <div class="card">

        <div class="card-title">
            Historial de Pagos
        </div>

        <table>

            <thead>

                <tr>

                    <th>Fecha</th>

                    <th>Método</th>

                    <th>Pago</th>

                    <th>Saldo</th>

                    <th>Estado</th>

                </tr>

            </thead>

            <tbody>

            @foreach($historialPagos as $historial)

                <tr>

                    <td>
                        {{ \Carbon\Carbon::parse($historial->fecha_pago)->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ ucfirst($historial->metodo_pago) }}
                    </td>

                    <td>
                        Bs {{ number_format($historial->monto_pagado, 2) }}
                    </td>

                    <td>
                        Bs {{ number_format($historial->pago_pendiente, 2) }}
                    </td>

                    <td>

                        @if($historial->estado_pago === 'pagado')

                            <span class="badge badge-success">
                                Pagado
                            </span>

                        @elseif($historial->estado_pago === 'parcial')

                            <span class="badge badge-warning">
                                Parcial
                            </span>

                        @else

                            <span class="badge badge-danger">
                                Pendiente
                            </span>

                        @endif

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

    {{-- OBSERVACIONES --}}
    <div class="card">

        <div class="card-title">
            Observaciones
        </div>

        {{ $pago->observaciones ?? 'Sin observaciones.' }}

    </div>

    {{-- RESUMEN FINAL --}}
    <div class="financial">

        <div class="financial-row">
            <strong>Total del Cobro:</strong>
            Bs {{ number_format($montoTotal, 2) }}
        </div>

        <div class="financial-row">
            <strong>Total Pagado:</strong>
            Bs {{ number_format($totalPagado, 2) }}
        </div>

        <div class="financial-row">
            <strong>Saldo Pendiente:</strong>
            Bs {{ number_format($saldoPendiente, 2) }}
        </div>

        <div style="margin-top: 14px;">

            @if($saldoPendiente <= 0)

                <span class="badge badge-success">
                    DEUDA COMPLETAMENTE PAGADA
                </span>

            @else

                <span class="badge badge-warning">
                    PAGO PARCIAL
                </span>

            @endif

        </div>

        <div class="financial-total">
            Bs {{ number_format($pago->monto_pagado, 2) }}
        </div>

    </div>

    {{-- FOOTER --}}
    <div class="footer">

        Sistema de Gestión Comercial ·
        Documento generado el
        {{ now()->format('d/m/Y H:i') }}

    </div>

</div>

</body>
</html>