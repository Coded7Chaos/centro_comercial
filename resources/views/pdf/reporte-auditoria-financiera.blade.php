<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Auditoría Financiera</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; background: #f3f4f6; }
        .container { padding: 22px; }
        .header { background: #0f172a; color: #fff; padding: 20px; border-radius: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0 0 6px; font-size: 24px; }
        .header p { margin: 2px 0; color: #cbd5e1; }
        .summary { width: 100%; margin-bottom: 16px; }
        .card { display: inline-block; width: 19%; vertical-align: top; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; margin-right: 0.7%; }
        .label { font-size: 8px; text-transform: uppercase; color: #6b7280; font-weight: bold; margin-bottom: 4px; }
        .value { font-size: 15px; font-weight: bold; color: #111827; }
        .success { color: #15803d; }
        .warning { color: #b45309; }
        .danger { color: #b91c1c; }
        .chart-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
        .chart-title { font-size: 13px; font-weight: bold; margin-bottom: 10px; }
        .bar-row { margin-bottom: 8px; }
        .bar-label { display: inline-block; width: 27%; white-space: nowrap; overflow: hidden; vertical-align: middle; }
        .bar-track { display: inline-block; width: 55%; height: 12px; background: #e5e7eb; border-radius: 999px; vertical-align: middle; }
        .bar-fill { height: 12px; background: #f43f5e; border-radius: 999px; }
        .bar-value { display: inline-block; width: 16%; text-align: right; vertical-align: middle; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th { background: #111827; color: #fff; padding: 7px 6px; text-align: left; font-size: 9px; }
        td { border-bottom: 1px solid #e5e7eb; padding: 6px; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .text-right { text-align: right; }
        .progress { width: 100%; height: 8px; background: #e5e7eb; border-radius: 999px; }
        .progress-fill { height: 8px; background: #16a34a; border-radius: 999px; }
        .footer { margin-top: 12px; color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Auditoría Financiera</h1>
        <p>Generado: {{ $generadoEn }}</p>
        <p>Infraestructura: {{ $infraestructura ?: 'Todas / sin filtro activo' }}</p>
    </div>

    <div class="summary">
        <div class="card">
            <div class="label">Contratos</div>
            <div class="value">{{ $totalContratos }}</div>
        </div>
        <div class="card">
            <div class="label">Clientes / locales</div>
            <div class="value">{{ $totalClientesLocales }}</div>
        </div>
        <div class="card">
            <div class="label">Deuda histórica</div>
            <div class="value danger">Bs. {{ number_format($totalDeuda, 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Pagado verificado</div>
            <div class="value success">Bs. {{ number_format($totalPagado, 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Saldo restante</div>
            <div class="value warning">Bs. {{ number_format($totalSaldo, 2) }}</div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-title">Saldos pendientes más altos</div>
        @foreach ($rows->sortByDesc('saldo')->take(8) as $row)
            @php
                $width = $totalSaldo > 0 ? max(2, min(100, ($row['saldo'] / $totalSaldo) * 100)) : 0;
            @endphp
            <div class="bar-row">
                <span class="bar-label">{{ $row['cliente'] }} / {{ $row['local'] }}</span>
                <span class="bar-track"><span class="bar-fill" style="width: {{ $width }}%;"></span></span>
                <span class="bar-value">Bs. {{ number_format($row['saldo'], 2) }}</span>
            </div>
        @endforeach
    </div>

    <table>
        <thead>
        <tr>
            <th>Inquilino</th>
            <th>Local</th>
            <th>Tienda</th>
            <th>Contratos</th>
            <th>Periodo</th>
            <th class="text-right">Monto contratos</th>
            <th class="text-right">Pagado</th>
            <th class="text-right">Saldo</th>
            <th>% pagado</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['cliente'] }}</td>
                <td>{{ $row['local'] }}</td>
                <td>{{ $row['tienda'] }}</td>
                <td>{{ $row['contratos'] }}</td>
                <td>{{ $row['fecha_inicio'] }} - {{ $row['fecha_fin'] }}</td>
                <td class="text-right">Bs. {{ number_format($row['deuda'], 2) }}</td>
                <td class="text-right">Bs. {{ number_format($row['pagado'], 2) }}</td>
                <td class="text-right"><strong>Bs. {{ number_format($row['saldo'], 2) }}</strong></td>
                <td>
                    {{ number_format($row['porcentaje_pagado'], 1) }}%
                    <div class="progress">
                        <div class="progress-fill" style="width: {{ min(100, $row['porcentaje_pagado']) }}%;"></div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No existen contratos para el filtro actual.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">
        Los pagos considerados son pagos verificados. El saldo restante se calcula como monto histórico de cobros menos pagos verificados.
    </div>
</div>
</body>
</html>
