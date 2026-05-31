<x-layouts.app title="Verificar Correo Electrónico" :hideNavbar="true">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto w-full max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Verifica tu correo electrónico
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Antes de continuar, por favor verifica tu cuenta
            </p>
        </div>

        <div class="mt-8 sm:mx-auto w-full max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="mb-4 text-sm text-gray-600">
                    {{ __('¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu dirección de correo haciendo clic en el enlace que te acabamos de enviar por correo? Si no lo recibiste, con gusto te enviaremos otro.') }}
                </div>

                @if (session('message'))
                    <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="mt-6 flex flex-col space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <div>
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Reenviar correo de verificación') }}
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="text-center">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 bg-transparent border-0 cursor-pointer">
                            {{ __('Cerrar sesión') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
