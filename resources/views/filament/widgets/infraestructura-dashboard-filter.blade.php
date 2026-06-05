<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Filtrar por infraestructura</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    wire:click="$set('selectedInfraId', null)"
                    class="rounded-xl px-4 py-1.5 text-xs font-bold border transition-all
                           {{ $selectedInfraId === null
                               ? 'bg-amber-500 text-white border-amber-500 shadow-sm'
                               : 'bg-white text-slate-600 border-slate-200 hover:border-amber-300 hover:text-amber-700' }}"
                >
                    Todas
                </button>
                @foreach($infraestructuras as $infra)
                <button
                    wire:click="$set('selectedInfraId', {{ $infra->id }})"
                    class="rounded-xl px-4 py-1.5 text-xs font-bold border transition-all
                           {{ $selectedInfraId === $infra->id
                               ? 'bg-amber-500 text-white border-amber-500 shadow-sm'
                               : 'bg-white text-slate-600 border-slate-200 hover:border-amber-300 hover:text-amber-700' }}"
                >
                    {{ $infra->nombre }}
                </button>
                @endforeach
            </div>

            @if($selectedInfraId)
            <span class="ml-auto text-[10px] font-semibold text-slate-400 italic">
                Mostrando datos de: <strong class="text-amber-600">{{ $activeLabel }}</strong>
            </span>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
