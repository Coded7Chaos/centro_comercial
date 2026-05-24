<x-layouts.app title="Iniciar Sesión" :hideNavbar="true">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto w-full max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Inicia sesión en tu cuenta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Accede a tu panel de gestión
            </p>
        </div>

        <div class="mt-8 sm:mx-auto w-full max-w-md">
            {{-- Botón Volver a la Tienda --}}
            <div class="mb-6">
                <a href="/" class="w-full flex items-center justify-center py-3 px-4 border border-indigo-600 rounded-xl text-sm font-black text-indigo-600 bg-white hover:bg-indigo-50 transition-all shadow-md group">
                    <x-heroicon-o-arrow-left class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" />
                    VOLVER A LA TIENDA
                </a>
            </div>

            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form class="space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Correo electrónico
                        </label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Contraseña
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                Recuérdame
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Ingresar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
