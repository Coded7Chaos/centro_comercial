<x-filament-panels::page>
<div class="min-h-[calc(100vh-10rem)] flex flex-col items-center justify-center px-4 py-12">

    {{-- ── HERO ─────────────────────────────────────────────────── --}}
    <div class="text-center mb-10 max-w-2xl">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-100 border border-amber-200 shadow-sm mb-5">
            <x-heroicon-o-building-office-2 class="w-8 h-8 text-amber-600" />
        </div>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight mb-2">
            Selecciona un establecimiento
        </h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">
            Elige el establecimiento con el que deseas trabajar.<br>
            Todos los datos se filtrarán según tu selección.
        </p>

        @if($activeId)
        <div class="mt-4">
            <a href="{{ route('filament.admin.pages.dashboard') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-600 hover:text-amber-700 underline underline-offset-2">
                <x-heroicon-m-arrow-left class="w-4 h-4" />
                Volver al panel sin cambiar
            </a>
        </div>
        @endif
    </div>

    {{-- ── GRID DE INFRAESTRUCTURAS ─────────────────────────────── --}}
    @if($infraestructuras->isEmpty())
        <div class="w-full max-w-md rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 py-16 text-center">
            <x-heroicon-o-building-office class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" />
            <p class="font-bold text-gray-500 dark:text-gray-400">No hay infraestructuras creadas aún.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1 mb-6">Crea la primera para comenzar a trabajar.</p>
            <a href="{{ route('filament.admin.resources.infraestructuras.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold px-5 py-2.5 text-sm shadow transition-colors">
                <x-heroicon-m-plus class="w-4 h-4" />
                Crear infraestructura
            </a>
        </div>
    @else
        <div class="w-full max-w-4xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($infraestructuras as $infra)
            <button
                wire:click="selectInfraestructura({{ $infra->id }})"
                wire:loading.attr="disabled"
                wire:target="selectInfraestructura({{ $infra->id }})"
                class="group relative flex flex-col text-left rounded-3xl border-2 p-6 shadow-sm
                       transition-all duration-200 cursor-pointer
                       hover:-translate-y-1 hover:shadow-lg
                       focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2
                       {{ $activeId === $infra->id
                           ? 'border-amber-400 bg-amber-50 dark:bg-amber-950/30 ring-2 ring-amber-300'
                           : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/60 hover:border-amber-300' }}"
            >
                {{-- Loading spinner --}}
                <div wire:loading wire:target="selectInfraestructura({{ $infra->id }})"
                     class="absolute inset-0 flex items-center justify-center rounded-3xl bg-white/70 dark:bg-gray-900/70 backdrop-blur-sm z-10">
                    <svg class="animate-spin w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>

                {{-- Badge activo --}}
                @if($activeId === $infra->id)
                <span class="absolute top-4 right-4 inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest bg-amber-500 text-white px-2.5 py-1 rounded-full">
                    <x-heroicon-m-check class="w-3 h-3" />
                    Activo
                </span>
                @endif

                {{-- Icono --}}
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl mb-4 transition-colors
                            {{ $activeId === $infra->id
                                ? 'bg-amber-500 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-400 group-hover:bg-amber-100 group-hover:text-amber-600' }}">
                    <x-heroicon-o-building-office-2 class="w-6 h-6" />
                </div>

                {{-- Nombre y ubicación --}}
                <h3 class="font-black text-gray-900 dark:text-white text-lg leading-tight mb-1 pr-12">
                    {{ $infra->nombre }}
                </h3>
                @if($infra->ubicacion)
                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mb-3">
                    <x-heroicon-m-map-pin class="w-3 h-3 shrink-0" />
                    {{ $infra->ubicacion }}
                </p>
                @else
                <div class="mb-3"></div>
                @endif

                {{-- Stats --}}
                <div class="mt-auto flex items-center gap-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="text-center">
                        <div class="text-lg font-black text-gray-900 dark:text-white">
                            {{ $infra->pisosInfraestructura->count() }}
                        </div>
                        <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                            {{ Str::plural('Piso', $infra->pisosInfraestructura->count()) }}
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="text-center">
                        <div class="text-lg font-black text-gray-900 dark:text-white">
                            {{ $infra->pisosInfraestructura->sum(fn($p) => $p->tiendas->count()) }}
                        </div>
                        <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                            Tiendas
                        </div>
                    </div>
                </div>

                {{-- Arrow --}}
                <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <x-heroicon-m-arrow-right class="w-4 h-4 text-amber-500" />
                </div>
            </button>
            @endforeach
        </div>
    @endif
</div>
</x-filament-panels::page>
