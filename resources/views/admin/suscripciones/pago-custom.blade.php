<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pago Inicial - Administración</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .card-hud {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 p-6 md:p-12">

    <div class="max-w-4xl mx-auto space-y-8" x-data="{
        metodoPago: 'efectivo',
        bancoOrigen: 'BCP',
        montoRequerido: {{ $pago_inicial }},
        
        init() {
            // Trigger automatic PDF contract download if parameter is present
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('download_pdf')) {
                // Wait briefly then download
                setTimeout(() => {
                    window.location.href = '{{ route('admin.suscripciones.contrato-descargar', $suscripcion->id) }}';
                }, 500);
            }
        }
    }">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">Registrar Pago Inicial</h1>
                <p class="text-sm text-slate-500 font-medium mt-1">Paso 2: Reportar cobro de adelanto y garantía para activar el arrendamiento</p>
            </div>
            <a href="/admin/suscripciones" 
                class="px-5 py-2.5 bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm">
                Omitir pago por ahora
            </a>
        </div>

        {{-- SUMMARY CARD --}}
        <div class="card-hud p-6 md:p-8 bg-slate-900 text-white border-none flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-1">
                <div class="text-[9px] font-black text-indigo-300 uppercase tracking-widest flex items-center gap-2">
                    @if($suscripcion->renovacion_de_id)
                        <span class="px-2 py-0.5 bg-amber-500 text-slate-900 rounded-md text-[8px] font-black uppercase tracking-wider">Renovación</span>
                    @endif
                    Suscripción Creada
                </div>
                <div class="text-xl font-black truncate">Local N° {{ $suscripcion->infraestructurasTienda?->numero }} — {{ $suscripcion->infraestructurasTienda?->nombre ?: 'Sin nombre' }}</div>
                <div class="text-xs text-slate-300 font-medium">Inquilino: {{ $suscripcion->cliente?->nombre_completo }} • Plazo: {{ $suscripcion->tipo }}</div>
            </div>
            <div class="space-y-1 text-left md:text-right border-t md:border-t-0 md:border-l border-slate-700 pt-4 md:pt-0 md:pl-8">
                <div class="text-[9px] font-black text-emerald-400 uppercase tracking-widest">Pago Inicial Requerido</div>
                <div class="text-3xl font-black text-emerald-400">Bs. {{ number_format($pago_inicial, 2) }}</div>
                <div class="text-[10px] text-slate-300 font-medium">Renta mensual: Bs. {{ number_format($monthlyRent, 2) }}</div>
            </div>
        </div>

        {{-- DOWNLOAD CONTRACT CARD --}}
        <div class="card-hud p-6 bg-white border border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-900">Contrato de Arrendamiento</h4>
                    <p class="text-xs text-slate-500 font-medium">Descarga el documento de arrendamiento generado automáticamente.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('admin.suscripciones.contrato-descargar', $suscripcion->id) }}" 
                    class="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 transition rounded-xl font-bold text-xs uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
                <a href="{{ route('admin.suscripciones.contrato-descargar', ['id' => $suscripcion->id, 'format' => 'word']) }}" 
                    class="px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition rounded-xl font-bold text-xs uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Word (.docx)
                </a>
            </div>
        </div>

        {{-- FORM CARD --}}
        <form action="{{ route('admin.suscripciones.pagar-custom', $suscripcion->id) }}" method="POST" enctype="multipart/form-data" class="card-hud p-6 md:p-10 space-y-8">
            @csrf

            <input type="hidden" name="suscripcion_cobro_id" value="{{ $cobro->id }}">

            {{-- PAYMENT VALUES GROUP --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- MONTO PAGADO --}}
                <div class="space-y-2">
                    <label for="monto_pagado" class="block text-xs font-black uppercase tracking-wider text-slate-500">Monto Cobrado (Bs.) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" required value="{{ old('monto_pagado', $pago_inicial) }}"
                        class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-bold text-lg text-emerald-700">
                    @error('monto_pagado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- FECHA PAGO --}}
                <div class="space-y-2">
                    <label for="fecha_pago" class="block text-xs font-black uppercase tracking-wider text-slate-500">Fecha de Pago <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_pago" id="fecha_pago" required value="{{ old('fecha_pago', today()->toDateString()) }}"
                        class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                    @error('fecha_pago') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- METODO PAGO --}}
            <div class="space-y-2">
                <label for="metodo_pago" class="block text-xs font-black uppercase tracking-wider text-slate-500">Método de Pago <span class="text-red-500">*</span></label>
                <select name="metodo_pago" id="metodo_pago" required x-model="metodoPago"
                    class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-bold cursor-pointer">
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia Bancaria</option>
                    <option value="qr">Código QR</option>
                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                </select>
                @error('metodo_pago') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- DYNAMIC METHOD FIELDS --}}
            
            {{-- EFECTIVO --}}
            <div class="space-y-4 border-t border-slate-100 pt-6" x-show="metodoPago === 'efectivo'">
                <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Detalles del Pago en Efectivo</h4>
                <div class="space-y-2">
                    <label for="nombre_pagador" class="block text-xs font-bold text-slate-500">Nombre de quien realiza el pago <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre_pagador" id="nombre_pagador" :required="metodoPago === 'efectivo'" value="{{ old('nombre_pagador') }}" placeholder="Ej. Juan Pérez"
                        class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                </div>
            </div>

            {{-- TRANSFERENCIA --}}
            <div class="space-y-6 border-t border-slate-100 pt-6" x-show="metodoPago === 'transferencia'">
                <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Detalles de Transferencia Bancaria</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="referencia" class="block text-xs font-bold text-slate-500">Número de Transacción / Clave de Rastreo <span class="text-red-500">*</span></label>
                        <input type="number" name="referencia" id="referencia" :required="metodoPago === 'transferencia'" value="{{ old('referencia') }}" placeholder="Ej. 10293847"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                    </div>
                    <div class="space-y-2">
                        <label for="banco_origen" class="block text-xs font-bold text-slate-500">Banco de Origen <span class="text-red-500">*</span></label>
                        <select name="banco_origen" id="banco_origen" x-model="bancoOrigen"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold cursor-pointer">
                            <option value="Banco Unión">Banco Unión</option>
                            <option value="BCP">BCP</option>
                            <option value="Mercantil Santa Cruz">Mercantil Santa Cruz</option>
                            <option value="BISA">BISA</option>
                            <option value="BNB">BNB</option>
                            <option value="Ganadero">Ganadero</option>
                            <option value="Ecofuturo">Ecofuturo</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="nombre_titular" class="block text-xs font-bold text-slate-500">Nombre del Titular de la Cuenta <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre_titular" id="nombre_titular" :required="metodoPago === 'transferencia'" value="{{ old('nombre_titular') }}" placeholder="Ej. Juan Pérez"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                    </div>
                    <div class="space-y-2" x-show="bancoOrigen === 'otro'">
                        <label for="otro_banco" class="block text-xs font-bold text-slate-500">Escriba el nombre del banco <span class="text-red-500">*</span></label>
                        <input type="text" name="otro_banco" id="otro_banco" :required="metodoPago === 'transferencia' && bancoOrigen === 'otro'" value="{{ old('otro_banco') }}" placeholder="Ej. Banco Fassil"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                    </div>
                </div>
            </div>

            {{-- QR --}}
            <div class="space-y-6 border-t border-slate-100 pt-6" x-show="metodoPago === 'qr'">
                <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Detalles de Operación QR</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="folio_qr" class="block text-xs font-bold text-slate-500">ID de Operación / Folio QR <span class="text-red-500">*</span></label>
                        <input type="text" name="folio_qr" id="folio_qr" :required="metodoPago === 'qr'" value="{{ old('folio_qr') }}" placeholder="Ej. QR-10293"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                    </div>
                    <div class="space-y-2">
                        <label for="billetera_origen" class="block text-xs font-bold text-slate-500">Billetera / Aplicación origen <span class="text-red-500">*</span></label>
                        <select name="billetera_origen" id="billetera_origen"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold cursor-pointer">
                            <option value="Yape">Yape</option>
                            <option value="Tigo Money">Tigo Money</option>
                            <option value="Soli">Soli</option>
                            <option value="Banca Móvil">Banca Móvil / QR Interbancario</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- TARJETA --}}
            <div class="space-y-6 border-t border-slate-100 pt-6" x-show="metodoPago === 'tarjeta'">
                <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Detalles de Tarjeta Crédito/Débito</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="codigo_autorizacion" class="block text-xs font-bold text-slate-500">Código de Autorización Pos <span class="text-red-500">*</span></label>
                        <input type="text" name="codigo_autorizacion" id="codigo_autorizacion" :required="metodoPago === 'tarjeta'" value="{{ old('codigo_autorizacion') }}" placeholder="Ej. Auth-123"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold">
                    </div>
                    <div class="space-y-2">
                        <label for="ultimos_4_digitos" class="block text-xs font-bold text-slate-500">Últimos 4 Dígitos de la Tarjeta <span class="text-red-500">*</span></label>
                        <input type="text" maxlength="4" name="ultimos_4_digitos" id="ultimos_4_digitos" :required="metodoPago === 'tarjeta'" value="{{ old('ultimos_4_digitos') }}" placeholder="Ej. 4321"
                            class="block w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 font-semibold text-center tracking-widest">
                    </div>
                </div>
            </div>

            {{-- FILE COMPROBANTE --}}
            <div class="space-y-2 border-t border-slate-100 pt-6">
                <label for="comprobante" class="block text-xs font-black uppercase tracking-wider text-slate-500">Comprobante de Pago (Opcional)</label>
                <input type="file" name="comprobante" id="comprobante" accept="image/*,application/pdf"
                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:uppercase file:bg-slate-900 file:text-white hover:file:bg-indigo-700 transition cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-1">Formatos admitidos: JPG, PNG, PDF. Tamaño máximo: 4MB.</p>
                @error('comprobante') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- SUBMIT BUTTONS --}}
            <div class="pt-6">
                <button type="submit"
                    class="w-full justify-center px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition duration-300 shadow-md flex items-center gap-2 cursor-pointer">
                    Registrar Pago y Finalizar
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

        </form>
    </div>

</body>
</html>
