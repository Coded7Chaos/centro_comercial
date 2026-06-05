<x-layouts.client title="Mis Marcas">

    <div class="space-y-10"
         x-data="{
             modal: false,
             deleteName: '',
             deleteUrl: '',
             openModal(name, url) {
                 this.deleteName = name;
                 this.deleteUrl  = url;
                 this.modal      = true;
             }
         }">

        {{-- TOAST éxito --}}
        @if(session('success'))
            <div x-data="{ visible: true }"
                 x-show="visible"
                 x-init="setTimeout(() => visible = false, 4000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-emerald-600 text-white px-5 py-4 rounded-2xl shadow-xl shadow-emerald-600/30 text-sm font-semibold max-w-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
                <button @click="visible = false" class="ml-2 opacity-70 hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif

        {{-- TOAST error --}}
        @if(session('error'))
            <div x-data="{ visible: true }"
                 x-show="visible"
                 x-init="setTimeout(() => visible = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-red-600 text-white px-5 py-4 rounded-2xl shadow-xl shadow-red-600/30 text-sm font-semibold max-w-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
                <button @click="visible = false" class="ml-2 opacity-70 hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif

        {{-- MODAL confirmación eliminar --}}
        <div x-show="modal" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="modal = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modal = false"></div>
            <div x-show="modal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 space-y-5">
                <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-2xl mx-auto">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="text-center space-y-1">
                    <h3 class="text-lg font-black text-slate-800">¿Eliminar marca?</h3>
                    <p class="text-sm text-slate-500">Estás a punto de eliminar <strong class="text-slate-700" x-text="'«' + deleteName + '»'"></strong>. Esta acción no se puede deshacer.</p>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="modal = false"
                            class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <form :action="deleteUrl" method="POST" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition shadow-sm shadow-red-600/30">
                            Sí, eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 1: MIS MARCAS PRIVADAS                               --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Mis Marcas</h3>
                    <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">Marcas privadas registradas por ti</p>
                </div>
                <a href="{{ route('cliente.marcas.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/25 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Marca
                </a>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-200/60 overflow-hidden shadow-sm">
                @if($misMarcas->isEmpty())
                    <div class="p-12 text-center text-slate-400 italic">
                        <svg class="w-12 h-12 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm font-semibold text-slate-500">Aún no has registrado ninguna marca propia.</p>
                        <p class="text-xs mt-1">Puedes crear una con el botón "Nueva Marca".</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-500">
                            <thead class="bg-slate-50 text-xs font-bold text-slate-700 uppercase tracking-widest border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4">Marca</th>
                                    <th class="px-6 py-4">Descripción</th>
                                    <th class="px-6 py-4">Estado</th>
                                    <th class="px-6 py-4">Creada</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @foreach($misMarcas as $m)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-6 py-4 flex items-center gap-4 text-slate-900">
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                                                @if($m->logo)
                                                    <img src="{{ Storage::url($m->logo) }}" class="w-full h-full object-cover" alt="{{ $m->nombre }}">
                                                @else
                                                    <span class="font-bold text-slate-400 text-xs">{{ strtoupper(substr($m->nombre, 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm">{{ $m->nombre }}</div>
                                                <span class="inline-block px-2 py-0.5 text-[8px] font-black uppercase bg-indigo-50 border border-indigo-100 text-indigo-700 rounded mt-0.5">Privada</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 text-xs max-w-[250px] truncate">
                                            {{ $m->descripcion ?: 'Sin descripción' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $m->estado === 'activo' ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-600' }}">
                                                {{ $m->estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-400 text-xs">
                                            {{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('cliente.marcas.edit', $m->id) }}"
                                                   class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                                <button type="button"
                                                        @click="openModal('{{ addslashes($m->nombre) }}', '{{ route('cliente.marcas.destroy', $m->id) }}')"
                                                        class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 2: MARCAS DEL ADMINISTRADOR                          --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="space-y-4">
            <div>
                <h3 class="text-xl font-black text-slate-800">Marcas del Administrador</h3>
                <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">Marcas públicas disponibles para todos los clientes — solo lectura</p>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-200/60 overflow-hidden shadow-sm">
                @if($marcasAdmin->isEmpty())
                    <div class="p-12 text-center text-slate-400 italic">
                        <svg class="w-12 h-12 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm font-semibold text-slate-500">El administrador aún no ha creado marcas públicas.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-500">
                            <thead class="bg-amber-50 text-xs font-bold text-amber-800 uppercase tracking-widest border-b border-amber-100">
                                <tr>
                                    <th class="px-6 py-4">Marca</th>
                                    <th class="px-6 py-4">Descripción</th>
                                    <th class="px-6 py-4">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @foreach($marcasAdmin as $m)
                                    <tr class="hover:bg-amber-50/30 transition">
                                        <td class="px-6 py-4 flex items-center gap-4 text-slate-900">
                                            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 overflow-hidden shrink-0 flex items-center justify-center">
                                                @if($m->logo)
                                                    <img src="{{ Storage::url($m->logo) }}" class="w-full h-full object-cover" alt="{{ $m->nombre }}">
                                                @else
                                                    <span class="font-bold text-amber-400 text-xs">{{ strtoupper(substr($m->nombre, 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm">{{ $m->nombre }}</div>
                                                <span class="inline-block px-2 py-0.5 text-[8px] font-black uppercase bg-amber-50 border border-amber-200 text-amber-700 rounded mt-0.5">Pública</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 text-xs max-w-[300px] truncate">
                                            {{ $m->descripcion ?: 'Sin descripción' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $m->estado === 'activo' ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-600' }}">
                                                {{ $m->estado }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>

</x-layouts.client>
