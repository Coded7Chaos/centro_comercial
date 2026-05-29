<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrato de Arrendamiento - Local {{ $tienda?->numero }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            color: #1f2937;
            background: #ffffff;
        }
        .text-center {
            text-align: center;
        }
        .text-justify {
            text-align: justify;
        }
        .bold {
            font-weight: bold;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #111827;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #4b5563;
            font-size: 12px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        .clause {
            margin-bottom: 15px;
        }
        .clause-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        .table-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-details th, .table-details td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .table-details th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .signature-container {
            margin-top: 60px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
        }
        .signature-box-right {
            width: 45%;
            float: right;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #111827;
            margin-top: 50px;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 50px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
            clear: both;
        }
    </style>
</head>
<body>

    <div class="header text-center">
        <h1>CONTRATO DE ARRENDAMIENTO COMERCIAL</h1>
        <p>{{ $infraestructura?->nombre ?? 'Mall Gran Vía' }} • Gestión de Alquileres</p>
    </div>

    <div class="text-justify">
        <p>Conste por el presente documento privado de Contrato de Arrendamiento Comercial, el cual se suscribe al tenor de las cláusulas siguientes:</p>
        
        <p><span class="bold">PRIMERA (PARTES CONTRATANTES):</span> Por una parte, la administración de <span class="bold">{{ $infraestructura?->nombre ?? 'Mall Gran Vía' }}</span>, representada por su administrador autorizado, a quien en lo sucesivo se denominará el <span class="bold">ARRENDADOR</span>; y por otra parte, el señor(a) <span class="bold">{{ $cliente?->nombre_completo }}</span> con cédula de identidad N° <span class="bold">{{ $cliente?->ci }}</span>, a quien en lo sucesivo se denominará el <span class="bold">ARRENDATARIO</span>, acuerdan celebrar el presente contrato.</p>
    </div>

    <div class="section-title">Detalles del Arrendamiento</div>
    <table class="table-details">
        <tr>
            <th>Infraestructura / Mall</th>
            <td>{{ $infraestructura?->nombre ?? 'N/A' }}</td>
            <th>Ubicación</th>
            <td>{{ $infraestructura?->ubicacion ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Piso / Nivel</th>
            <td>{{ $piso?->nombre ?? 'N/A' }}</td>
            <th>Local Comercial</th>
            <td>Local N° {{ $tienda?->numero }} - {{ $tienda?->nombre ?? 'Sin nombre' }}</td>
        </tr>
        <tr>
            <th>Tamaño del Local</th>
            <td>{{ ucfirst($tienda?->tamano ?? 'pequeño') }}</td>
            <th>Marca Principal</th>
            <td>{{ $marca?->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Precio de Alquiler</th>
            <td class="bold">Bs. {{ number_format($suscripcion->precio, 2) }}</td>
            <th>Tipo de Suscripción</th>
            <td>{{ ucfirst($suscripcion->tipo) }}</td>
        </tr>
        <tr>
            <th>Fecha de Inicio</th>
            <td>{{ \Carbon\Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y') }}</td>
            <th>Fecha de Finalización</th>
            <td>{{ \Carbon\Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="section-title">Cláusulas del Contrato</div>
    <div class="text-justify">
        <div class="clause">
            <div class="clause-title">SEGUNDA: OBJETO Y DESTINO.</div>
            El ARRENDADOR otorga en calidad de arrendamiento comercial el local identificado en los detalles superiores a favor del ARRENDATARIO. El ARRENDATARIO se compromete a destinar dicho local comercial única y exclusivamente para la explotación de actividades comerciales acordes a la marca <span class="bold">{{ $marca?->nombre ?? 'General' }}</span>, quedando estrictamente prohibido cambiar el giro comercial sin autorización expresa y escrita del ARRENDADOR.
        </div>

        <div class="clause">
            <div class="clause-title">TERCERA: CANON Y FORMA DE PAGO.</div>
            El canon de arrendamiento acordado es el monto detallado en la tabla superior, el cual deberá ser pagado periódicamente según el tipo de suscripción (<span class="bold">{{ $suscripcion->tipo }}</span>) dentro de los primeros cinco (5) días hábiles de cada periodo de facturación. Los pagos se realizarán mediante los canales de transferencia, depósito o cajas habilitadas por el ARRENDADOR.
        </div>

        <div class="clause">
            <div class="clause-title">CUARTA: PLAZO DE DURACIÓN Y RENOVACIÓN.</div>
            El plazo de duración del presente contrato es determinado, rigiendo desde el <span class="bold">{{ \Carbon\Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y') }}</span> hasta el <span class="bold">{{ \Carbon\Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') }}</span>. Al vencimiento de dicho plazo, y en caso de que el ARRENDATARIO desee continuar con la ocupación del local comercial, las partes deberán manifestar su voluntad de <span class="bold">renovar</span> el contrato al menos quince (15) días antes de su finalización, formalizando un nuevo periodo y tarifa.
        </div>

        <div class="clause">
            <div class="clause-title">QUINTA: MANTENIMIENTO Y MEJORAS.</div>
            El ARRENDATARIO declara recibir el local en perfectas condiciones de habitabilidad y funcionamiento y se obliga a devolverlo en el mismo estado. Cualquier mejora estructural requerirá el consentimiento previo y por escrito del ARRENDADOR.
        </div>
    </div>

    <p class="text-justify">En conformidad y aceptación de las cláusulas detalladas, las partes firman el presente contrato por duplicado en fecha {{ $fechaDocumento }}.</p>

    <div class="signature-container">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p><span class="bold">EL ARRENDADOR</span><br>Administración {{ $infraestructura?->nombre ?? 'Mall' }}</p>
        </div>
        <div class="signature-box-right">
            <div class="signature-line"></div>
            <p><span class="bold">EL ARRENDATARIO</span><br>CI: {{ $cliente?->ci }}</p>
        </div>
    </div>

    <div class="footer">
        Documento generado automáticamente por el sistema de control comercial del {{ $infraestructura?->nombre ?? 'Mall' }}.
    </div>

</body>
</html>
