<x-layouts.client title="Mi Panel de Gestión">

    <div class="space-y-8">
        
        <!-- BIENVENIDA -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-[2.5rem] p-8 md:p-12 text-white shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
            <div class="relative z-10 space-y-4">
                <span class="px-4 py-1.5 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-bold uppercase tracking-widest">
                    Portal de Socios
                </span>
                <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-none">
                    ¡Bienvenido, {{ $cliente->nombre_completo }}!
                </h1>
                <p class="text-slate-300 max-w-xl text-sm md:text-base font-medium">
                    Administra tus locales comerciales, actualiza tu catálogo de productos al público y consulta tus facturas desde tu panel privado.
                </p>
            </div>
        </div>

        <!-- RESUMEN KPI -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Locales Comerciales</div>
                    <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $tiendas->count() }}</div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Productos Publicados</div>
                    <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $cantProductos }}</div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE TIENDAS Y DEUDAS -->
        <div class="grid grid-cols-1 gap-8">
            
            <!-- MIS TIENDAS -->
            <div class="bg-white rounded-[2rem] border border-slate-200/60 p-6 space-y-6">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Mis Locales Comerciales
                </h3>
                
                @if($tiendas->isEmpty())
                    <p class="text-slate-400 italic text-sm text-center py-6">No tienes locales asignados por la administración en este momento.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($tiendas as $tienda)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm md:text-base">{{ $tienda->nombre ?: 'Local '.$tienda->numero }}</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $tienda->piso->infraestructura->nombre }} • {{ $tienda->piso->nombre }} • Local {{ $tienda->numero }}
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 border border-emerald-100 text-emerald-700">
                                    {{ $tienda->estado->estado ?? 'Alquilada' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- HISTORIAL DE CONTRATOS -->
        <div class="bg-white rounded-[2rem] border border-slate-200/60 p-6 space-y-6">
            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Contratos de Alquiler y Suscripciones
            </h3>

            @if($suscripciones->isEmpty())
                <p class="text-slate-400 italic text-sm text-center py-6">No posees contratos registrados en el sistema.</p>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-sm text-slate-500">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-700 uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Local</th>
                                <th class="px-6 py-4">Tarifa / Periodo</th>
                                <th class="px-6 py-4">Vigencia</th>
                                <th class="px-6 py-4">Documentos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($suscripciones as $sub)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 text-slate-900">
                                        <div class="font-bold">Local {{ $sub->infraestructurasTienda?->numero }}</div>
                                        <div class="text-xs text-slate-400 font-medium mt-0.5">{{ $sub->infraestructurasTienda?->nombre ?: 'Sin nombre' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-900">
                                        <div class="font-extrabold">Bs. {{ number_format($sub->precio, 2) }}</div>
                                        <span class="inline-block px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded border border-indigo-200 bg-indigo-50 text-indigo-700 mt-1">
                                            {{ $sub->tipo }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-900 text-xs">
                                        {{ \Carbon\Carbon::parse($sub->fecha_inicio)->format('d/m/Y') }} al <br>
                                        {{ \Carbon\Carbon::parse($sub->fecha_fin)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 space-y-2">
                                        <a href="{{ route('pdf.contrato', $sub->id) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Contrato Digital PDF
                                        </a>
                                        @if($sub->contrato_firmado)
                                            <br>
                                            <a href="{{ Storage::url($sub->contrato_firmado) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 hover:text-emerald-800 font-bold mt-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Ver Contrato Firmado
                                            </a>
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

</x-layouts.client>
