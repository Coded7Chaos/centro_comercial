<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Suscripciones;
use App\Models\SuscripcionesCobros;
use App\Models\SuscripcionesPagos;
use App\Models\SuscripcionesTarifas;
use App\Observers\SuscripcionObserver;
use App\Support\ActiveInfraestructura;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class SuscripcionCustomController extends Controller
{
    private function checkAdmin()
    {
        if (! auth()->user() || ! auth()->user()->hasRole(['super_admin', 'admin'])) {
            abort(403);
        }
    }

    public function create()
    {
        $this->checkAdmin();
        $clientes = Clientes::with('user')->get()->sortBy(function ($c) {
            return $c->nombre_completo;
        });

        // Load shops filtered to the active infraestructura so created subscriptions
        // are always visible in the list (which scopes by the same session value).
        $infraId = ActiveInfraestructura::getId();
        $tiendasQuery = InfraestructurasTiendas::with(['piso.infraestructura']);
        if ($infraId) {
            $tiendasQuery->whereHas('piso', fn ($q) => $q->where('infraestructura_id', $infraId));
        }
        $tiendasRaw = $tiendasQuery->get();
        $tiendasData = [];

        foreach ($tiendasRaw as $tienda) {
            $subActiva = Suscripciones::where('infraestructuras_tienda_id', $tienda->id)
                ->where('fecha_fin', '>=', now()->toDateString())
                ->orderBy('fecha_fin', 'desc')
                ->first();

            $diasParaLiberar = 0;
            $fechaLiberacion = null;
            $disponible = true;

            if ($subActiva) {
                $disponible = false;
                $fechaLiberacion = $subActiva->fecha_fin;
                $diasParaLiberar = max(0, now()->startOfDay()->diffInDays(Carbon::parse($fechaLiberacion)->startOfDay(), false));
            }

            $tiendasData[] = [
                'tienda' => $tienda,
                'disponible' => $disponible,
                'fecha_liberacion' => $fechaLiberacion,
                'dias_para_liberar' => $diasParaLiberar,
            ];
        }

        // Sort: available first, then sorted by remaining days to release (lowest to highest)
        usort($tiendasData, function ($a, $b) {
            if ($a['disponible'] && ! $b['disponible']) {
                return -1;
            }
            if (! $a['disponible'] && $b['disponible']) {
                return 1;
            }
            if (! $a['disponible'] && ! $b['disponible']) {
                return $a['dias_para_liberar'] <=> $b['dias_para_liberar'];
            }

            return $a['tienda']->numero <=> $b['tienda']->numero;
        });

        return view('admin.suscripciones.crear-custom', compact('clientes', 'tiendasData'));
    }

    public function getTiendaPrecio(Request $request, $tienda_id)
    {
        $this->checkAdmin();
        $tienda = InfraestructurasTiendas::findOrFail($tienda_id);
        $tamano = (float) $tienda->tamano;

        $duracionValor = (int) $request->query('duracion_valor', 1);
        $duracionUnidad = $request->query('duracion_unidad', 'meses');
        $totalMonths = $duracionUnidad === 'años' ? $duracionValor * 12 : $duracionValor;

        $calc = SuscripcionesTarifas::calcularAlquiler($tamano, $totalMonths);

        return response()->json([
            'id' => $tienda->id,
            'numero' => $tienda->numero,
            'nombre' => $tienda->nombre,
            'tamano' => $tamano,
            'precio_mensual_base' => $calc['precio_mensual_base'],
            'descuento_porcentaje' => $calc['descuento_porcentaje'],
            'descuento_monto_mensual' => $calc['descuento_monto_mensual'],
            'precio_mensual_con_descuento' => $calc['precio_mensual_con_descuento'],
            'precio_total_sin_descuento' => $calc['precio_total_sin_descuento'],
            'precio_total_con_descuento' => $calc['precio_total_con_descuento'],
            'pago_inicial' => $totalMonths > 1 ? $calc['precio_mensual_con_descuento'] * 2 : $calc['precio_mensual_con_descuento'],
            'etiqueta' => $calc['etiqueta'],
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'infraestructuras_tienda_id' => 'required|exists:infraestructuras_tiendas,id',
            'duracion_valor' => 'required|integer|min:1',
            'duracion_unidad' => 'required|in:meses,años',
            'fecha_inicio' => 'required|date',
        ]);

        $tienda = InfraestructurasTiendas::findOrFail($request->infraestructuras_tienda_id);
        $fecha_inicio = Carbon::parse($request->fecha_inicio);

        $duracionValor = (int) $request->duracion_valor;
        $totalMonths = $request->duracion_unidad === 'años' ? $duracionValor * 12 : $duracionValor;

        $fecha_fin = $fecha_inicio->copy()->addMonthsNoOverflow($totalMonths)->subDay()->toDateString();
        $fecha_inicio_str = $fecha_inicio->toDateString();

        // Anti-duplicates verification
        $existe = Suscripciones::where('infraestructuras_tienda_id', $tienda->id)
            ->where(function ($query) use ($fecha_inicio_str, $fecha_fin) {
                $query->whereBetween('fecha_inicio', [$fecha_inicio_str, $fecha_fin])
                    ->orWhereBetween('fecha_fin', [$fecha_inicio_str, $fecha_fin])
                    ->orWhere(function ($sub) use ($fecha_inicio_str, $fecha_fin) {
                        $sub->where('fecha_inicio', '<=', $fecha_inicio_str)
                            ->where('fecha_fin', '>=', $fecha_fin);
                    });
            })
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['infraestructuras_tienda_id' => 'Ya existe una suscripción activa para esta tienda en ese periodo.']);
        }

        // Calculate Pricing using rent calculator logic
        $calc = SuscripcionesTarifas::calcularAlquiler((float) $tienda->tamano, $totalMonths);
        $precio_total = $calc['precio_total_con_descuento'];

        // Map Duration description
        $duracionDesc = $duracionValor.' '.($duracionValor == 1 ? ($request->duracion_unidad === 'meses' ? 'mes' : 'año') : $request->duracion_unidad);

        // Save Subscription
        $suscripcion = Suscripciones::create([
            'cliente_id' => $request->cliente_id,
            'marca_id' => null, // Brands not assigned on creation as requested
            'tipo' => $duracionDesc,
            'precio' => $precio_total,
            'fecha_inicio' => $fecha_inicio_str,
            'fecha_fin' => $fecha_fin,
            'infraestructuras_tienda_id' => $tienda->id,
            'infraestructuras_piso_id' => $tienda->infraestructura_piso_id,
        ]);

        // Sync floor layout status
        SuscripcionObserver::syncTienda($tienda->id);

        // Redirect to Step 2: Payment registration, triggering PDF download parameter
        return redirect()->route('admin.suscripciones.pago-custom', [
            'id' => $suscripcion->id,
            'download_pdf' => 1,
        ]);
    }

    public function descargarContrato($id)
    {
        $this->checkAdmin();
        $suscripcion = Suscripciones::with([
            'cliente.user',
            'infraestructurasTienda.piso.infraestructura',
        ])->findOrFail($id);

        $cliente = $suscripcion->cliente;
        $tienda = $suscripcion->infraestructurasTienda;
        $marca = null; // brands omitted
        $piso = $tienda?->piso;
        $infraestructura = $piso?->infraestructura;
        $fechaDocumento = now()->translatedFormat('d \d\e F \d\e Y');

        if (request('format') === 'word') {
            $phpWord = new PhpWord;
            $section = $phpWord->addSection([
                'marginLeft' => 1440,
                'marginRight' => 1440,
                'marginTop' => 1440,
                'marginBottom' => 1440,
            ]);

            $fontStyle = ['name' => 'Arial', 'size' => 11, 'color' => '1F2937'];
            $boldFont = ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '1F2937'];
            $underlineBoldFont = ['name' => 'Arial', 'size' => 11, 'bold' => true, 'underline' => 'single', 'color' => '1F2937'];

            $titleFont = ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => '111827'];
            $subtitleFont = ['name' => 'Arial', 'size' => 11, 'color' => '4B5563'];
            $sectionTitleFont = ['name' => 'Arial', 'size' => 13, 'bold' => true, 'color' => '111827'];

            $paraStyle = ['alignment' => 'justify', 'spaceAfter' => 120, 'lineHeight' => 1.15];
            $centerParaStyle = ['alignment' => 'center', 'spaceAfter' => 120, 'lineHeight' => 1.15];
            $leftParaStyle = ['alignment' => 'left', 'spaceAfter' => 120, 'lineHeight' => 1.15];

            // Header
            $section->addText('CONTRATO DE ARRENDAMIENTO COMERCIAL', $titleFont, $centerParaStyle);
            $section->addText(($infraestructura?->nombre ?? 'Mall Gran Vía').' • Gestión de Alquileres', $subtitleFont, $centerParaStyle);
            $section->addTextBreak(1);

            // Introduction
            $section->addText('Conste por el presente documento privado de Contrato de Arrendamiento Comercial, el cual se suscribe al tenor de las cláusulas siguientes:', $fontStyle, $paraStyle);

            // PRIMERA CLAUSE
            $textRun1 = $section->addTextRun($paraStyle);
            $textRun1->addText('PRIMERA (PARTES CONTRATANTES): ', $boldFont);
            $textRun1->addText('Por una parte, la administración de ', $fontStyle);
            $textRun1->addText($infraestructura?->nombre ?? 'Mall Gran Vía', $boldFont);
            $textRun1->addText(', representada por su administrador autorizado, a quien en lo sucesivo se denominará el ', $fontStyle);
            $textRun1->addText('ARRENDADOR', $boldFont);
            $textRun1->addText('; y por otra parte, el señor(a) ', $fontStyle);
            $textRun1->addText($cliente?->nombre_completo, $boldFont);
            $textRun1->addText(' con cédula de identidad N° ', $fontStyle);
            $textRun1->addText($cliente?->ci, $boldFont);
            $textRun1->addText(', a quien en lo sucesivo se denominará el ', $fontStyle);
            $textRun1->addText('ARRENDATARIO', $boldFont);
            $textRun1->addText(', acuerdan celebrar el presente contrato.', $fontStyle);

            $section->addTextBreak(1);

            // Details title
            $section->addText('Detalles del Arrendamiento', $sectionTitleFont, $leftParaStyle);

            // Details Table
            $tableStyle = [
                'borderSize' => 6,
                'borderColor' => 'D1D5DB',
                'cellMargin' => 120,
            ];
            $phpWord->addTableStyle('DetailsTable', $tableStyle);
            $table = $section->addTable('DetailsTable');

            $thStyle = ['bgColor' => 'F3F4F6'];
            $thFont = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '111827'];
            $tdFont = ['name' => 'Arial', 'size' => 10, 'color' => '1F2937'];
            $tdFontBold = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '1F2937'];

            // Row 1: Mall / Ubicación
            $table->addRow();
            $table->addCell(2500, $thStyle)->addText('Infraestructura / Mall', $thFont);
            $table->addCell(2500)->addText($infraestructura?->nombre ?? 'N/A', $tdFont);
            $table->addCell(2500, $thStyle)->addText('Ubicación', $thFont);
            $table->addCell(2500)->addText($infraestructura?->ubicacion ?? 'N/A', $tdFont);

            // Row 2: Piso / Local
            $table->addRow();
            $table->addCell(2500, $thStyle)->addText('Piso / Nivel', $thFont);
            $table->addCell(2500)->addText($piso?->nombre ?? 'N/A', $tdFont);
            $table->addCell(2500, $thStyle)->addText('Local Comercial', $thFont);
            $table->addCell(2500)->addText('Local N° '.$tienda?->numero.($tienda?->nombre ? " - {$tienda->nombre}" : ' - Sin nombre'), $tdFont);

            // Row 3: Tamaño / Precio
            $table->addRow();
            $table->addCell(2500, $thStyle)->addText('Tamaño del Local', $thFont);
            $table->addCell(2500)->addText(ucfirst($tienda?->tamano ?? 'pequeño'), $tdFont);
            $table->addCell(2500, $thStyle)->addText('Precio de Alquiler', $thFont);
            $table->addCell(2500)->addText('Bs. '.number_format($suscripcion->precio, 2), $tdFontBold);

            // Row 4: Fechas
            $table->addRow();
            $table->addCell(2500, $thStyle)->addText('Fecha de Inicio', $thFont);
            $table->addCell(2500)->addText(Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y'), $tdFont);
            $table->addCell(2500, $thStyle)->addText('Fecha de Finalización', $thFont);
            $table->addCell(2500)->addText(Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y'), $tdFont);

            // Row 5: Duración del contrato (colspan)
            $table->addRow();
            $table->addCell(2500, $thStyle)->addText('Duración del contrato', $thFont);
            $table->addCell(7500, ['gridSpan' => 3])->addText(ucfirst($suscripcion->tipo), $tdFont);

            $section->addTextBreak(1);

            // Clauses title
            $section->addText('Cláusulas del Contrato', $sectionTitleFont, $leftParaStyle);

            // Clauses Content
            // SEGUNDA
            $textRun2 = $section->addTextRun($paraStyle);
            $textRun2->addText('SEGUNDA: OBJETO Y DESTINO. ', $underlineBoldFont);
            $textRun2->addText('El ARRENDADOR otorga en calidad de arrendamiento comercial el local identificado en los detalles superiores a favor del ARRENDATARIO. El ARRENDATARIO se compromete a destinar dicho local comercial única y exclusivamente para la explotación de actividades comerciales acordes a la marca ', $fontStyle);
            $textRun2->addText($marca?->nombre ?? 'General', $boldFont);
            $textRun2->addText(', quedando estrictamente prohibido cambiar el giro comercial sin autorización expresa y escrita del ARRENDADOR.', $fontStyle);

            $tipoStr = strtolower($suscripcion->tipo);
            $esMayorAMensual = true;
            if (str_contains($tipoStr, '1 mes') || str_contains($tipoStr, 'mensual') || str_contains($tipoStr, '1 semanas') || str_contains($tipoStr, 'semanal')) {
                $esMayorAMensual = false;
            }

            // TERCERA
            $textRun3 = $section->addTextRun($paraStyle);
            $textRun3->addText('TERCERA: CANON Y FORMA DE PAGO. ', $underlineBoldFont);
            $textRun3->addText('El canon de arrendamiento acordado es el monto detallado en la tabla superior, el cual deberá ser pagado periódicamente según la duración del contrato (', $fontStyle);
            $textRun3->addText($suscripcion->tipo, $boldFont);
            $textRun3->addText(') dentro de los primeros cinco (5) días hábiles de cada periodo de facturación. Los pagos se realizarán mediante los canales de transferencia, depósito o cajas habilitadas por el ARRENDADOR.', $fontStyle);

            $currentClauseNumber = 4;
            if ($esMayorAMensual) {
                // CUARTA
                $textRun4 = $section->addTextRun($paraStyle);
                $textRun4->addText('CUARTA: DEPÓSITO DE GARANTÍA. ', $underlineBoldFont);
                if ($suscripcion->renovacion_de_id) {
                    $textRun4->addText('Se deja constancia de que el depósito de garantía entregado por el ARRENDATARIO en el contrato original se mantiene y transfiere plenamente para garantizar las obligaciones del presente contrato de renovación, por lo cual no se requiere un desembolso adicional por este concepto. Por tanto, el pago inicial requerido es de un (1) mes de alquiler correspondiente al primer mes adelantado.', $fontStyle);
                } else {
                    $textRun4->addText('El ARRENDATARIO entregará al ARRENDADOR al momento de la firma de este contrato la suma equivalente a un (1) mes de alquiler en calidad de garantía de cumplimiento del presente contrato. Esta suma no devengará intereses y será devuelta al ARRENDATARIO al término del contrato, previa deducción de cualquier deuda pendiente o costo de reparación de daños ocasionados en el local comercial. Por tanto, el pago inicial requerido es de dos (2) meses de alquiler (primer mes adelantado más el mes de garantía).', $fontStyle);
                }
                $currentClauseNumber = 5;
            }

            // PLAZO
            $numWord = ($currentClauseNumber == 5) ? 'QUINTA' : 'CUARTA';
            $textRun5 = $section->addTextRun($paraStyle);
            $textRun5->addText("{$numWord}: PLAZO DE DURACIÓN Y RENOVACIÓN. ", $underlineBoldFont);
            $textRun5->addText('El plazo de duración del presente contrato es determinado, rigiendo desde el ', $fontStyle);
            $textRun5->addText(Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y'), $boldFont);
            $textRun5->addText(' hasta el ', $fontStyle);
            $textRun5->addText(Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y'), $boldFont);
            $textRun5->addText('. Al vencimiento de dicho plazo, y en caso de que el ARRENDATARIO desee continuar con la ocupación del local comercial, las partes deberán manifestar su voluntad de ', $fontStyle);
            $textRun5->addText('renovar', $boldFont);
            $textRun5->addText(' el contrato al menos quince (15) días antes de su finalización, formalizando un nuevo periodo y tarifa.', $fontStyle);

            $currentClauseNumber++;

            // MANTENIMIENTO
            $numWord2 = ($currentClauseNumber == 6) ? 'SEXTA' : 'QUINTA';
            $textRun6 = $section->addTextRun($paraStyle);
            $textRun6->addText("{$numWord2}: MANTENIMIENTO Y MEJORAS. ", $underlineBoldFont);
            $textRun6->addText('El ARRENDATARIO declara recibir el local en perfectas condiciones de habitabilidad y funcionamiento y se obliga a devolverlo en el mismo estado. Cualquier mejora estructural requerirá el consentimiento previo y por escrito del ARRENDADOR.', $fontStyle);

            // Acceptance
            $section->addTextBreak(1);
            $section->addText("En conformidad y aceptación de las cláusulas detalladas, las partes firman el presente contrato por duplicado en fecha {$fechaDocumento}.", $fontStyle, $paraStyle);

            $section->addTextBreak(2);

            // Signatures
            $sigTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 100]);
            $sigTable->addRow();
            $cellLeft = $sigTable->addCell(5000);
            $cellLeft->addText('_______________________________', $fontStyle, ['alignment' => 'center']);
            $cellLeft->addText('EL ARRENDADOR', $boldFont, ['alignment' => 'center']);
            $cellLeft->addText('Administración '.($infraestructura?->nombre ?? 'Mall'), $fontStyle, ['alignment' => 'center']);

            $cellRight = $sigTable->addCell(5000);
            $cellRight->addText('_______________________________', $fontStyle, ['alignment' => 'center']);
            $cellRight->addText('EL ARRENDATARIO', $boldFont, ['alignment' => 'center']);
            $cellRight->addText('CI: '.$cliente?->ci, $fontStyle, ['alignment' => 'center']);

            // Footer
            $section->addTextBreak(2);
            $section->addText('Documento generado automáticamente por el sistema de control comercial del '.($infraestructura?->nombre ?? 'Mall').'.', ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => '9CA3AF'], $centerParaStyle);

            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $fileName = "contrato-alquiler-local-{$tienda?->numero}.docx";
            $tempFile = storage_path('app/'.uniqid('contrato_').'.docx');
            $objWriter->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        }

        $pdf = Pdf::loadView('pdf.contrato', [
            'suscripcion' => $suscripcion,
            'cliente' => $cliente,
            'marca' => $marca,
            'tienda' => $tienda,
            'piso' => $piso,
            'infraestructura' => $infraestructura,
            'fechaDocumento' => $fechaDocumento,
        ]);

        return $pdf->download("contrato-alquiler-local-{$tienda?->numero}.pdf");
    }

    public function pago($id)
    {
        $this->checkAdmin();
        $suscripcion = Suscripciones::with(['cliente.user', 'infraestructurasTienda'])->findOrFail($id);

        // Find the automatically generated cobro for this subscription
        $cobro = SuscripcionesCobros::where('suscripcion_id', $suscripcion->id)
            ->where('estado', '!=', 'anulado')
            ->orderBy('fecha_vencimiento')
            ->orderBy('id')
            ->first();

        if (! $cobro) {
            $suscripcion->generarCobrosMensuales();

            $cobro = SuscripcionesCobros::where('suscripcion_id', $suscripcion->id)
                ->where('estado', '!=', 'anulado')
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->firstOrFail();
        }

        // Determine number of months
        $tipo = strtolower($suscripcion->tipo);
        preg_match('/\d+/', $tipo, $matches);
        $duracion = isset($matches[0]) ? (int) $matches[0] : 1;

        if (str_contains($tipo, 'año')) {
            $totalMonths = $duracion * 12;
        } else {
            $totalMonths = $duracion;
        }

        $monthlyRent = $suscripcion->precio / max(1, $totalMonths);

        // Required initial payment:
        // If it is a renewal contract, no security deposit is required (only 1 month rent).
        // Otherwise: if 1 month rent: 1 month. If > 1 month rent: 2 months (1 month rent + 1 month security deposit)
        $isRenewal = $suscripcion->renovacion_de_id !== null;
        $pago_inicial = ($totalMonths > 1 && ! $isRenewal) ? $monthlyRent * 2 : $monthlyRent;

        return view('admin.suscripciones.pago-custom', compact('suscripcion', 'cobro', 'pago_inicial', 'monthlyRent'));
    }

    public function storePayment(Request $request, $id)
    {
        $this->checkAdmin();
        $comprobanteRule = $request->metodo_pago !== 'efectivo'
            ? 'required|file|mimes:jpg,jpeg,png,pdf|max:4096'
            : 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096';

        $request->validate([
            'suscripcion_cobro_id' => 'required|exists:suscripciones_cobros,id',
            'monto_pagado'         => 'required|numeric|min:1',
            'fecha_pago'           => 'required|date',
            'metodo_pago'          => 'required|in:efectivo,transferencia,qr,tarjeta',
            'comprobante'          => $comprobanteRule,
        ]);

        $cobro = SuscripcionesCobros::findOrFail($request->suscripcion_cobro_id);

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes', 'public');
        }

        // Set standard referencia field and method-specific fields
        $referenciaVal = null;
        if ($request->metodo_pago === 'transferencia') {
            $referenciaVal = $request->referencia;
        } elseif ($request->metodo_pago === 'qr') {
            $referenciaVal = $request->folio_qr;
        } elseif ($request->metodo_pago === 'tarjeta') {
            $referenciaVal = $request->codigo_autorizacion;
        }

        // Create Payment record
        $pago = SuscripcionesPagos::create([
            'suscripcion_cobro_id' => $cobro->id,
            'monto_pagado' => (float) $request->monto_pagado,
            'pago_pendiente' => max(0, (float) $cobro->monto - (float) $request->monto_pagado),
            'fecha_pago' => $request->fecha_pago,
            'fecha_hora_operacion' => now(),
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $referenciaVal,
            'nombre_pagador' => $request->metodo_pago === 'efectivo' ? $request->nombre_pagador : null,
            'numero_transaccion' => $request->metodo_pago === 'transferencia' ? $request->referencia : null,
            'banco_origen' => $request->metodo_pago === 'transferencia' ? ($request->banco_origen === 'otro' ? 'Otro' : $request->banco_origen) : null,
            'banco_otro' => $request->metodo_pago === 'transferencia' && $request->banco_origen === 'otro' ? $request->otro_banco : null,
            'titular_transferencia' => $request->metodo_pago === 'transferencia' ? $request->nombre_titular : null,
            'codigo_qr' => $request->metodo_pago === 'qr' ? $request->folio_qr : null,
            'billetera_qr' => $request->metodo_pago === 'qr' ? $request->billetera_origen : null,
            'codigo_autorizacion' => $request->metodo_pago === 'tarjeta' ? $request->codigo_autorizacion : null,
            'ultimos_4_tarjeta' => $request->metodo_pago === 'tarjeta' ? $request->ultimos_4_digitos : null,
            'comprobante' => $comprobantePath,
            'estado_verificacion' => 'verificado',
            'monto_total' => $cobro->monto,
            'observaciones' => 'Pago inicial registrado en asistente personalizado.',
            'estado_snapshot' => 'pagado',
        ]);

        // Replicate custom splitting/deferring logic
        $totalPagado = $cobro->pagos()->sum('monto_pagado');
        $saldoPendiente = (float) $cobro->monto - $totalPagado;
        if ($saldoPendiente < 0) {
            $saldoPendiente = 0;
        }

        if ($saldoPendiente > 0) {
            $fechaVencimientoCobro = SuscripcionesCobros::fechaSaldoPendiente($cobro);

            SuscripcionesCobros::create([
                'suscripcion_id' => $cobro->suscripcion_id,
                'concepto' => SuscripcionesCobros::conceptoSaldoPendiente($cobro),
                'monto' => $saldoPendiente,
                'fecha_inicio' => $pago->fecha_pago ?? now()->toDateString(),
                'fecha_vencimiento' => $fechaVencimientoCobro,
                'estado' => 'pendiente',
                'observaciones' => 'Cobro generado de saldo pendiente del pago #'.$pago->id,
                'es_parcial' => true,
            ]);

            $cobro->update([
                'monto' => $totalPagado,
                'saldo_pendiente' => 0,
                'estado' => 'pagado',
                'estado_snapshot' => 'pagado',
            ]);

            $pago->update([
                'pago_pendiente' => 0,
                'estado_snapshot' => 'pagado',
            ]);
        } else {
            $pago->update([
                'pago_pendiente' => 0,
                'estado_snapshot' => 'pagado',
            ]);

            $cobro->update([
                'saldo_pendiente' => 0,
                'estado' => 'pagado',
                'estado_snapshot' => 'pagado',
            ]);
        }

        // Sync layout shop status
        SuscripcionObserver::syncTienda($cobro->suscripcion?->infraestructuras_tienda_id);

        return redirect('/admin/suscripciones')
            ->with('success', 'Contrato y pago inicial registrados exitosamente.');
    }

    public function renovar($id)
    {
        $this->checkAdmin();
        $parentSub = Suscripciones::with([
            'cliente.user',
            'infraestructurasTienda.piso.infraestructura',
        ])->findOrFail($id);

        $cliente = $parentSub->cliente;
        $tienda = $parentSub->infraestructurasTienda;

        // Start date defaults to the day after parent subscription ends
        $defaultStartDate = Carbon::parse($parentSub->fecha_fin)->addDay()->toDateString();

        return view('admin.suscripciones.renovar-custom', compact('parentSub', 'cliente', 'tienda', 'defaultStartDate'));
    }

    public function storeRenewal(Request $request, $id)
    {
        $this->checkAdmin();
        $parentSub = Suscripciones::findOrFail($id);

        $request->validate([
            'duracion_valor' => 'required|integer|min:1',
            'duracion_unidad' => 'required|in:meses,años',
            'fecha_inicio' => 'required|date',
        ]);

        $tienda = InfraestructurasTiendas::findOrFail($parentSub->infraestructuras_tienda_id);
        $fecha_inicio = Carbon::parse($request->fecha_inicio);

        $duracionValor = (int) $request->duracion_valor;
        $totalMonths = $request->duracion_unidad === 'años' ? $duracionValor * 12 : $duracionValor;

        $fecha_fin = $fecha_inicio->copy()->addMonthsNoOverflow($totalMonths)->subDay()->toDateString();
        $fecha_inicio_str = $fecha_inicio->toDateString();

        // Overlap verification excluding parent contract itself
        $existe = Suscripciones::where('infraestructuras_tienda_id', $tienda->id)
            ->where('id', '!=', $parentSub->id)
            ->where(function ($query) use ($fecha_inicio_str, $fecha_fin) {
                $query->whereBetween('fecha_inicio', [$fecha_inicio_str, $fecha_fin])
                    ->orWhereBetween('fecha_fin', [$fecha_inicio_str, $fecha_fin])
                    ->orWhere(function ($sub) use ($fecha_inicio_str, $fecha_fin) {
                        $sub->where('fecha_inicio', '<=', $fecha_inicio_str)
                            ->where('fecha_fin', '>=', $fecha_fin);
                    });
            })
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['fecha_inicio' => 'Ya existe otra suscripción activa para esta tienda en ese periodo.']);
        }

        // Calculate Pricing using rent calculator logic
        $calc = SuscripcionesTarifas::calcularAlquiler((float) $tienda->tamano, $totalMonths);
        $precio_total = $calc['precio_total_con_descuento'];

        // Map Duration description
        $duracionDesc = $duracionValor.' '.($duracionValor == 1 ? ($request->duracion_unidad === 'meses' ? 'mes' : 'año') : $request->duracion_unidad);

        // Save Subscription
        $suscripcion = Suscripciones::create([
            'cliente_id' => $parentSub->cliente_id,
            'marca_id' => $parentSub->marca_id, // maintain brand if any
            'tipo' => $duracionDesc,
            'precio' => $precio_total,
            'fecha_inicio' => $fecha_inicio_str,
            'fecha_fin' => $fecha_fin,
            'infraestructuras_tienda_id' => $tienda->id,
            'infraestructuras_piso_id' => $tienda->infraestructura_piso_id,
            'renovacion_de_id' => $parentSub->id,
        ]);

        // Sync floor layout status
        SuscripcionObserver::syncTienda($tienda->id);

        // Redirect to Step 2: Payment registration, triggering PDF download parameter
        return redirect()->route('admin.suscripciones.pago-custom', [
            'id' => $suscripcion->id,
            'download_pdf' => 1,
        ]);
    }
}
