<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - {{ $tienda->marca->nombre ?? 'Tienda '.$tienda->numero }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Catálogo: {{ $tienda->marca->nombre ?? 'Tienda '.$tienda->numero }}</h1>
            <a href="{{ route('directorio.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">&larr; Volver al Directorio</a>
        </div>

        @if($productos->isEmpty())
            <div class="bg-white p-10 rounded-lg shadow-sm text-center">
                <p class="text-gray-500 text-lg">Esta tienda aún no ha publicado productos en su catálogo.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($productos as $producto)
                <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        @if($producto->imagenes->first())
                            <img src="{{ Storage::url($producto->imagenes->first()->ruta) }}" alt="{{ $producto->nombre }}" class="h-full w-full object-cover">
                        @else
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ $producto->nombre }}</h3>
                        <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $producto->descripcion }}</p>
                        <div class="text-xl font-bold text-indigo-700">Bs. {{ number_format($producto->precio, 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
