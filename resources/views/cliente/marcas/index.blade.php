<x-layouts.client title="Mis Marcas Privadas">

    <div class="space-y-8">
        
        <!-- HEADER ACTIONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-black text-slate-800">Mis Marcas Privadas</h3>
                <p class="text-xs text-slate-400 font-bold tracking-widest mt-0.5">Administra tus marcas registradas de forma privada</p>
            </div>
            
            <a href="{{ route('cliente.marcas.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/25 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar Nueva Marca
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        <!-- BRANDS LIST TABLE -->
        <div class="bg-white rounded-[2rem] border border-slate-200/60 overflow-hidden shadow-sm">
            @if($marcas->isEmpty())
                <div class="p-12 text-center text-slate-400 italic">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Aún no has registrado marcas privadas. ¡Registra tu primera marca para empezar a vender tus productos!
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-500">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-700 uppercase tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Marca</th>
                                <th class="px-6 py-4">Descripción</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4">Fecha de Creación</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($marcas as $m)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <!-- Marca -->
                                    <td class="px-6 py-4 flex items-center gap-4 text-slate-900">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                                            @if($m->logo)
                                                <img src="{{ Storage::url($m->logo) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="font-bold text-slate-400 text-xs">
                                                    {{ strtoupper(substr($m->nombre, 0, 2)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm">{{ $m->nombre }}</div>
                                            <span class="inline-block px-2 py-0.5 text-[8px] font-black uppercase bg-indigo-50 border border-indigo-100 text-indigo-700 rounded mt-0.5">Privada</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Descripción -->
                                    <td class="px-6 py-4 text-slate-500 text-xs max-w-[250px] truncate">
                                        {{ $m->descripcion ?: 'Sin descripción' }}
                                    </td>

                                    <!-- Estado -->
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $m->estado === 'activo' ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-600' }}">
                                            {{ $m->estado }}
                                        </span>
                                    </td>

                                    <!-- Creado -->
                                    <td class="px-6 py-4 text-slate-400 text-xs">
                                        {{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('cliente.marcas.edit', $m->id) }}" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            
                                            <form method="POST" action="{{ route('cliente.marcas.destroy', $m->id) }}" onsubmit="return confirm('¿Está seguro de eliminar esta marca de sus registros?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
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

</x-layouts.client>
