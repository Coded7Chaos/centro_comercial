<x-layouts.app title="Configura tu contraseña" :hideNavbar="true">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">

        <div class="sm:mx-auto w-full max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Bienvenido/a, {{ $user->nombres }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Elige una contraseña para acceder a tu cuenta
            </p>
        </div>

        <div class="mt-8 sm:mx-auto w-full max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Preservamos la firma en la URL para que el middleware 'signed' valide el POST --}}
                <form class="space-y-6"
                      action="{{ request()->fullUrl() }}"
                      method="POST">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Correo electrónico
                        </label>
                        <div class="mt-1">
                            <input id="email" type="email" value="{{ $user->email }}" disabled
                                class="appearance-none block w-full px-3 py-2 border border-gray-200 rounded-md shadow-sm bg-gray-50 text-gray-500 sm:text-sm cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Nueva contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password"
                                required autocomplete="new-password"
                                placeholder="Mínimo 8 caracteres"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror">
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirmar contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                required autocomplete="new-password"
                                placeholder="Repite la contraseña"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                            Guardar contraseña y entrar
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-layouts.app>
