<div class="bg-slate-50/50 min-h-screen p-4 sm:p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Detalles del Contrato de Arrendamiento</h1>
                <p class="text-sm text-slate-500 font-medium mt-1">
                    Suscripción N° {{ $record->id }} • 
                    @if($record->fecha_fin >= now()->toDateString())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Activo</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">Vencido</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ \App\Filament\Resources\Suscripciones\SuscripcionesResource::getUrl('index') }}" 
                    class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al listado
                </a>
                <a href="{{ route('pdf.contrato', $record->id) }}" target="_blank"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white transition rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar Contrato PDF
                </a>
            </div>
        </div>

        @php
            $tipo = strtolower($record->tipo);
            if (str_contains($tipo, 'mes') || str_contains($tipo, 'año') || preg_match('/\d+/', $tipo)) {
                preg_match('/\d+/', $tipo, $matches);
                $duracion = isset($matches[0]) ? (int)$matches[0] : 1;
                if (str_contains($tipo, 'año')) {
                    $totalMonths = $duracion * 12;
                } else {
                    $totalMonths = $duracion;
                }
            } else {
                $totalMonths = match($tipo) {
                    'semanal' => 0.25,
                    'mensual' => 1,
                    'bimestral' => 2,
                    'trimestral' => 3,
                    'semestral' => 6,
                    'anual' => 12,
                    default => 1,
                };
            }
            $monthlyRent = $record->precio / max(0.1, $totalMonths);
            $pagoInicial = $totalMonths > 1 ? $monthlyRent * 2 : $monthlyRent;
        @endphp

        {{-- INFO CARD --}}
        <div class="bg-white rounded-3xl border border-slate-200 p-6 md:p-10 shadow-sm space-y-8">
            
            {{-- CLIENT INFO --}}
            <div class="space-y-2">
                <span class="block text-xs font-black uppercase tracking-wider text-slate-400">Cliente (Inquilino)</span>
                <div class="block w-full border border-slate-100 rounded-xl px-4 py-3 text-sm bg-slate-50 font-semibold text-slate-700">
                    Cliente #{{ $record->cliente?->id }} — {{ $record->cliente?->nombre_completo }} (CI: {{ $record->cliente?->ci }})
                </div>
            </div>

            {{-- SHOP INFO --}}
            <div class="space-y-2">
                <span class="block text-xs font-black uppercase tracking-wider text-slate-400">Local Comercial (Tienda)</span>
                <div class="block w-full border border-slate-100 rounded-xl px-4 py-3 text-sm bg-slate-50 font-semibold text-slate-700">
                    Local N° {{ $record->infraestructurasTienda?->numero }} - {{ $record->infraestructurasTienda?->nombre ?: 'Sin nombre' }} 
                    ({{ $record->infraestructurasTienda?->tamano }}m² — {{ $record->infraestructurasTienda?->piso?->nombre }})
                </div>
            </div>

            {{-- TERMS AND DATES --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="space-y-2">
                    <span class="block text-xs font-black uppercase tracking-wider text-slate-400">Duración</span>
                    <div class="block w-full border border-slate-100 rounded-xl px-4 py-3 text-sm bg-slate-50 font-semibold text-slate-700 text-center uppercase tracking-wider">
                        {{ $record->tipo }}
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="block text-xs font-black uppercase tracking-wider text-slate-400">Fecha de Inicio</span>
                    <div class="block w-full border border-slate-100 rounded-xl px-4 py-3 text-sm bg-slate-50 font-semibold text-slate-700 text-center">
                        {{ \Carbon\Carbon::parse($record->fecha_inicio)->format('d/m/Y') }}
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="block text-xs font-black uppercase tracking-wider text-slate-400">Fecha de Vencimiento</span>
                    <div class="block w-full border border-slate-100 rounded-xl px-4 py-3 text-sm bg-slate-50 font-semibold text-slate-700 text-center">
                        {{ \Carbon\Carbon::parse($record->fecha_fin)->format('d/m/Y') }}
                    </div>
                </div>

            </div>

            {{-- FINANCIAL BREAKDOWN --}}
            <div class="border-t border-slate-100 pt-8 space-y-6">
                <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Resumen y Desglose Financiero
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-150">
                    <div class="space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Alquiler Mensual Base</div>
                        <div class="text-2xl font-black text-slate-900">Bs. {{ number_format($monthlyRent, 2, ',', '.') }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">Tarifa del Local</div>
                    </div>
                    <div class="space-y-1 border-t md:border-t-0 md:border-x border-slate-200 py-4 md:py-0 md:px-6">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Precio Total Contrato</div>
                        <div class="text-2xl font-black text-indigo-700">Bs. {{ number_format($record->precio, 2, ',', '.') }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">Monto total por {{ $record->tipo }}</div>
                    </div>
                    <div class="space-y-1 md:pl-6">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pago Inicial Requerido</div>
                        <div class="text-2xl font-black text-emerald-700">Bs. {{ number_format($pagoInicial, 2, ',', '.') }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">{{ $totalMonths > 1 ? '1 mes adelanto + 1 mes garantía' : '1 mes de alquiler' }}</div>
                    </div>
                </div>

                {{-- GARANTÍA APLICADA --}}
                @if($totalMonths > 1)
                <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-5 space-y-3">
                    <div class="flex items-start gap-2.5 text-xs text-indigo-900 font-medium">
                        <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <div class="space-y-1">
                            <span class="font-black uppercase tracking-wider block text-[10px] text-indigo-700">Garantía Aplicada</span>
                            <p class="text-pretty font-normal">Este contrato contempla un **Depósito de Garantía** de Bs. {{ number_format($monthlyRent, 2, ',', '.') }} equivalente a un mes de renta.</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- SIGNED CONTRACT FILE --}}
                @if($record->contrato_firmado)
                <div class="space-y-2 mt-4 pt-4 border-t border-slate-100">
                    <span class="block text-xs font-black uppercase tracking-wider text-slate-400">Contrato Firmado Cargado</span>
                    <div class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                        <svg class="w-8 h-8 text-indigo-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-700 truncate">{{ basename($record->contrato_firmado) }}</p>
                        </div>
                        <a href="{{ asset('storage/' . $record->contrato_firmado) }}" target="_blank"
                            class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm flex items-center gap-2">
                            Ver documento
                        </a>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
