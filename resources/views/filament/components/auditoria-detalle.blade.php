@php
    /** @var \Spatie\Activitylog\Models\Activity $log */
    $changes = $log->attribute_changes ?? [];
    if ($changes instanceof \Illuminate\Support\Collection) {
        $changes = $changes->toArray();
    }
    $attrs = $changes['attributes'] ?? [];
    $old   = $changes['old'] ?? [];

    $accion = match($log->description) {
        'created' => 'Creó',
        'updated' => 'Actualizó',
        'deleted' => 'Eliminó',
        default   => ucfirst((string)$log->description),
    };

    $accionColor = match($log->description) {
        'created' => 'text-green-700 bg-green-100',
        'updated' => 'text-yellow-700 bg-yellow-100',
        'deleted' => 'text-red-700 bg-red-100',
        default   => 'text-gray-700 bg-gray-100',
    };

    // Lista de campos que aparecen en attributes u old
    $campos = array_unique(array_merge(array_keys((array) $attrs), array_keys((array) $old)));
@endphp

<div class="space-y-5 pb-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3 bg-white dark:bg-gray-900">
            <div class="text-[11px] font-medium uppercase text-gray-500">Fecha</div>
            <div class="font-bold text-gray-900 dark:text-white">
                {{ $log->created_at->format('d/m/Y H:i:s') }}
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3 bg-white dark:bg-gray-900">
            <div class="text-[11px] font-medium uppercase text-gray-500">Acción</div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold mt-1 {{ $accionColor }}">{{ $accion }}</span>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3 bg-white dark:bg-gray-900">
            <div class="text-[11px] font-medium uppercase text-gray-500">Categoría</div>
            <div class="font-bold text-gray-900 dark:text-white">{{ $log->log_name }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3 bg-white dark:bg-gray-900">
            <div class="text-[11px] font-medium uppercase text-gray-500">Modelo</div>
            <div class="font-bold text-gray-900 dark:text-white">
                {{ $log->subject_type ? class_basename($log->subject_type) : '—' }}
                @if($log->subject_id) <span class="text-gray-500">#{{ $log->subject_id }}</span> @endif
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3 bg-white dark:bg-gray-900 md:col-span-2">
            <div class="text-[11px] font-medium uppercase text-gray-500">Ejecutado por</div>
            <div class="font-bold text-gray-900 dark:text-white">
                {{ $log->causer?->nombres ?? 'Sistema / sin usuario' }}
                @if($log->causer?->email)
                    <span class="text-xs text-gray-500 font-normal">({{ $log->causer->email }})</span>
                @endif
            </div>
        </div>
    </div>

    @if(count($campos) > 0)
        <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Cambios registrados</h3>
            <div class="border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white text-left">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Campo</th>
                            <th class="px-3 py-2 font-semibold">Antes</th>
                            <th class="px-3 py-2 font-semibold">Después</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach($campos as $campo)
                            @php
                                $antes   = $old[$campo] ?? null;
                                $despues = $attrs[$campo] ?? null;
                                $antesStr   = is_scalar($antes)   ? (string) $antes   : json_encode($antes);
                                $despuesStr = is_scalar($despues) ? (string) $despues : json_encode($despues);
                            @endphp
                            <tr class="bg-white dark:bg-gray-900">
                                <td class="px-3 py-2 font-medium text-gray-700 dark:text-gray-200">{{ $campo }}</td>
                                <td class="px-3 py-2 text-red-700 dark:text-red-300">
                                    <code class="text-xs">{{ $antesStr === '' || $antesStr === null ? '—' : $antesStr }}</code>
                                </td>
                                <td class="px-3 py-2 text-green-700 dark:text-green-300">
                                    <code class="text-xs">{{ $despuesStr === '' || $despuesStr === null ? '—' : $despuesStr }}</code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-sm italic text-gray-500">No hay diferencias de atributos registradas.</div>
    @endif
</div>
