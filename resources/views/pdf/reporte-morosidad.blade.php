<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Morosidad</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; background: #f3f4f6; }
        .container { padding: 22px; }
        .header { background: #111827; color: #fff; padding: 20px; border-radius: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0 0 6px; font-size: 24px; }
        .header p { margin: 2px 0; color: #d1d5db; }
        .summary { width: 100%; margin-bottom: 16px; }
        .card { display: inline-block; width: 19%; vertical-align: top; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; margin-right: 0.7%; }
        .card-danger { border-color: #fecaca; background: #fff1f2; }
        .label { font-size: 8px; text-transform: uppercase; color: #6b7280; font-weight: bold; margin-bottom: 4px; }
        .value { font-size: 15px; font-weight: bold; color: #111827; }
        .danger { color: #b91c1c; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th { background: #111827; color: #fff; padding: 7px 6px; text-align: left; font-size: 9px; }
        td { border-bottom: 1px solid #e5e7eb; padding: 6px; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 3px 7px; border-radius: 999px; font-size: 8px; font-weight: bold; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .footer { margin-top: 12px; color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Reporte de Morosidad</h1>
        <p>Generado: {{ $generadoEn }}</p>
        <p>Infraestructura: {{ $infraestructura ?: 'Todas / sin filtro activo' }}</p>
    </div>

    <div class="summary">
        <div class="card card-danger">
            <div class="label">Deuda vencida</div>
            <div class="value danger">Bs. {{ number_format($totalSaldo, 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Monto total</div>
            <div class="value">Bs. {{ number_format($totalMonto, 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Pagado</div>
            <div class="value">Bs. {{ number_format($totalPagado, 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Clientes morosos</div>
            <div class="value">{{ $totalClientes }}</div>
        </div>
        <div class="card">
            <div class="label">Cobros en mora</div>
            <div class="value">{{ $totalCobros }}</div>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Cliente</th>
            <th>Concepto</th>
            <th>Local</th>
            <th class="text-right">Monto</th>
            <th class="text-right">Pagado</th>
            <th class="text-right">Saldo</th>
            <th>Venció</th>
            <th>Días</th>
            <th>Estado</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['cliente'] }}</td>
                <td>{{ $row['concepto'] }}</td>
                <td>{{ $row['local'] }}</td>
                <td class="text-right">Bs. {{ number_format($row['monto'], 2) }}</td>
                <td class="text-right">Bs. {{ number_format($row['pagado'], 2) }}</td>
                <td class="text-right"><strong>Bs. {{ number_format($row['saldo'], 2) }}</strong></td>
                <td>{{ $row['fecha_vencimiento'] }}</td>
                <td>{{ $row['dias_atraso'] }} días</td>
                <td>
                    <span class="badge {{ $row['estado'] === 'Parcial' ? 'badge-warning' : 'badge-danger' }}">
                        {{ $row['estado'] }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No existen cobros en mora para el filtro actual.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">
        Este reporte refleja cobros vencidos con saldo pendiente al momento de la generación.
    </div>
</div>
</body>
</html>
