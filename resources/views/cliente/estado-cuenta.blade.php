<x-layouts.client title="Estado de Cuenta y Finanzas">

    <div class="space-y-8">
        
        <div>
            <h3 class="text-xl font-black text-slate-800">Mi Estado de Cuenta</h3>
            <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">Control de cobros periódicos de alquiler y reporte de transacciones</p>
        </div>

        <!-- COBROS LIST -->
        <div class="bg-white rounded-[2rem] border border-slate-200/60 overflow-hidden shadow-sm">
            @if($cobros->isEmpty())
                <p class="p-12 text-center text-slate-400 italic">No posees registros de facturación o cobros en este momento.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-500">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-700 uppercase tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Concepto / Mes</th>
                                <th class="px-6 py-4">Monto total</th>
                                <th class="px-6 py-4">Pagado</th>
                                <th class="px-6 py-4">Restante</th>
                                <th class="px-6 py-4">Fecha Vencimiento</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($cobros as $cobro)
                                @php
                                    $pagado = $cobro->pagos->sum('monto_pagado');
                                    $restante = max(0, $cobro->monto - $pagado);
                                    $estado = strtolower($cobro->estado);
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 text-slate-900">
                                        <div class="font-bold text-sm">{{ $cobro->concepto }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium mt-0.5">
                                            Local {{ $cobro->suscripcion?->infraestructurasTienda?->numero }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-slate-900 font-bold">
                                        Bs. {{ number_format($cobro->monto, 2) }}
                                    </td>

                                    <td class="px-6 py-4 text-emerald-600 font-bold">
                                        Bs. {{ number_format($pagado, 2) }}
                                    </td>

                                    <td class="px-6 py-4 text-rose-600 font-black">
                                        Bs. {{ number_format($restante, 2) }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-500 text-xs">
                                        {{ \Carbon\Carbon::parse($cobro->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="inline-block px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider border 
                                            {{ $estado === 'pagado' ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : '' }}
                                            {{ $estado === 'parcial' ? 'bg-amber-50 border-amber-100 text-amber-700' : '' }}
                                            {{ $estado === 'pendiente' ? 'bg-slate-50 border-slate-200 text-slate-600' : '' }}
                                            {{ $estado === 'vencido' ? 'bg-rose-50 border-rose-100 text-rose-700' : '' }}
                                            {{ $estado === 'anulado' ? 'bg-gray-100 border-gray-200 text-gray-400' : '' }}
                                        ">
                                            {{ $cobro->estado }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @if($restante > 0 && $estado !== 'anulado')
                                            <button type="button" 
                                                    onclick="openPaymentModal({{ $cobro->id }}, '{{ $cobro->concepto }}', {{ $restante }})"
                                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition active:scale-95">
                                                Reportar Pago
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400 italic font-bold">Ninguna</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <!-- MODAL DE PAGO -->
    <div id="payment-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-[2.5rem] border border-slate-200/50 shadow-2xl max-w-lg w-full overflow-hidden flex flex-col transform transition">
            
            <!-- Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
                <div>
                    <h4 class="font-black text-lg">Reportar Pago de Alquiler</h4>
                    <p class="text-[10px] text-slate-300 font-bold uppercase tracking-wider mt-0.5" id="modal-concepto"></p>
                </div>
                <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-white p-2 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('cliente.reportar-pago') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="suscripcion_cobro_id" id="modal-cobro-id">

                <!-- Info panel -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-700">Monto Restante de la Factura:</span>
                    <span class="font-black text-lg text-indigo-950" id="modal-monto"></span>
                </div>

                <!-- Monto a pagar -->
                <div class="space-y-1">
                    <label for="modal-monto-input" class="block text-xs font-bold text-slate-600">Monto del Pago (Bs.)</label>
                    <input type="number" step="0.01" min="0.10" name="monto_pagado" id="modal-monto-input" required 
                           class="w-full rounded-xl border-slate-200 py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>

                <!-- Método de Pago -->
                <div class="space-y-1">
                    <label for="metodo_pago" class="block text-xs font-bold text-slate-600">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago" required onchange="toggleMethodFields()"
                            class="w-full rounded-xl border-slate-200 py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="qr">Pago Simple QR</option>
                        <option value="efectivo">Depósito / Efectivo</option>
                        <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                    </select>
                </div>

                <!-- Campos dinámicos según el método -->
                <div id="method-fields" class="space-y-4">
                    <!-- Transferencia -->
                    <div id="field-transferencia" class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="numero_transaccion" class="block text-xs font-bold text-slate-600">Código de Operación</label>
                            <input type="text" name="numero_transaccion" id="numero_transaccion" placeholder="Ej: 125487" 
                                   class="w-full rounded-xl border-slate-200 py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <div class="space-y-1">
                            <label for="banco_origen" class="block text-xs font-bold text-slate-600">Banco de Origen</label>
                            <input type="text" name="banco_origen" id="banco_origen" placeholder="Ej: Banco Unión" 
                                   class="w-full rounded-xl border-slate-200 py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                    </div>

                    <!-- Efectivo -->
                    <div id="field-efectivo" class="space-y-1 hidden">
                        <label for="nombre_pagador" class="block text-xs font-bold text-slate-600">Nombre del Depositante</label>
                        <input type="text" name="nombre_pagador" id="nombre_pagador" placeholder="Ej: Juan Pérez" 
                               class="w-full rounded-xl border-slate-200 py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>

                <!-- Recibo / Comprobante -->
                <div class="space-y-1">
                    <label for="comprobante" class="block text-xs font-bold text-slate-600">Comprobante de Pago (PDF/Imagen)</label>
                    <input type="file" name="comprobante" id="comprobante" required 
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closePaymentModal()" 
                            class="px-5 py-3 rounded-2xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold text-sm transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/20 transition">
                        Reportar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT DE INTERACCIÓN MODAL -->
    <script>
        function openPaymentModal(cobroId, conceptoText, montoRestante) {
            document.getElementById('modal-cobro-id').value = cobroId;
            document.getElementById('modal-concepto').innerText = conceptoText;
            document.getElementById('modal-monto').innerText = 'Bs. ' + Number(montoRestante).toFixed(2);
            document.getElementById('modal-monto-input').value = Number(montoRestante).toFixed(2);
            document.getElementById('payment-modal').classList.remove('hidden');
            toggleMethodFields();
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }

        function toggleMethodFields() {
            const method = document.getElementById('metodo_pago').value;
            const fieldTransferencia = document.getElementById('field-transferencia');
            const fieldEfectivo = document.getElementById('field-efectivo');

            // Reset
            fieldTransferencia.classList.add('hidden');
            fieldEfectivo.classList.add('hidden');

            document.getElementById('numero_transaccion').required = false;
            document.getElementById('banco_origen').required = false;
            document.getElementById('nombre_pagador').required = false;

            if (method === 'transferencia') {
                fieldTransferencia.classList.remove('hidden');
                document.getElementById('numero_transaccion').required = true;
                document.getElementById('banco_origen').required = true;
            } else if (method === 'efectivo') {
                fieldEfectivo.classList.remove('hidden');
                document.getElementById('nombre_pagador').required = true;
            }
        }
    </script>

</x-layouts.client>
