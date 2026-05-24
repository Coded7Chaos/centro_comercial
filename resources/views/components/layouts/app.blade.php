<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Centro Comercial' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50 text-gray-900">
        @if(!isset($hideNavbar) || !$hideNavbar)
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="/" class="text-xl font-bold text-indigo-600">Centro Comercial</a>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="/" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Inicio</a>
                            <a href="{{ route('directorio.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Directorio</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @auth
                            <div class="flex items-center space-x-4">
                                @if(Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('admin'))
                                    <a href="/admin" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Panel Admin</a>
                                @else
                                    <a href="{{ route('cliente.dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Mi Panel</a>
                                @endif
                                <span class="text-sm text-gray-500">|</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Cerrar Sesión</button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Iniciar Sesión
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
        @endif

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-white mt-12 border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-base text-gray-400">&copy; {{ date('Y') }} Centro Comercial. Gestión Profesional de Locales.</p>
            </div>
        </footer>
    </body>
</html>
