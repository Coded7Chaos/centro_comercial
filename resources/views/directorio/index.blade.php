<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio del Centro Comercial</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-center mb-10 text-indigo-700">Directorio de Tiendas</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($tiendas as $tienda)
            <a href="{{ route('directorio.catalogo', $tienda->id) }}" class="block bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-20 h-20 mx-auto bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800">{{ $tienda->marca->nombre ?? 'Tienda '.$tienda->numero }}</h2>
                    <p class="text-gray-500 mt-2">Piso: {{ $tienda->piso->nombre ?? 'N/A' }} | Local: {{ $tienda->numero }}</p>
                    <span class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Ver Catálogo</span>
                </div>
            </a>
            @endforeach
        </div>
        
        @if($tiendas->isEmpty())
        <div class="text-center py-10">
            <p class="text-gray-500 text-lg">Actualmente no hay tiendas en el directorio.</p>
        </div>
        @endif
    </div>
</body>
</html>
