<x-filament-panels::page>
    <div class="max-w-4xl mx-auto space-y-6">
        
        {{-- HEADER CARD --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-200 dark:border-gray-800 p-8">
            <div class="flex items-center gap-6 mb-8">
                <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-2xl text-primary-600 dark:text-primary-400">
                    <x-heroicon-o-user-plus class="w-10 h-10" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $userId ? 'Editar Administrador' : 'Nuevo Administrador' }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Complete la información detallada para gestionar los accesos administrativos del sistema.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Nombres --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Nombres <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition">
                            <x-heroicon-o-user class="w-5 h-5" />
                        </div>
                        <input type="text" wire:model="nombres" placeholder="Ej. Juan"
                            class="block w-full pl-11 border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-2xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3 transition">
                    </div>
                    @error('nombres') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Apellido Paterno --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Apellido Paterno <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="apellido_paterno" placeholder="Ej. Pérez"
                        class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-2xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3 transition">
                    @error('apellido_paterno') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Apellido Materno --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Apellido Materno <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="apellido_materno" placeholder="Ej. García"
                        class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-2xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3 transition">
                    @error('apellido_materno') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Email --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition">
                            <x-heroicon-o-envelope class="w-5 h-5" />
                        </div>
                        <input type="email" wire:model="email" placeholder="admin@ejemplo.com"
                            class="block w-full pl-11 border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-2xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3 transition">
                    </div>
                    @error('email') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Rol --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Rol asignado <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition">
                            <x-heroicon-o-shield-check class="w-5 h-5" />
                        </div>
                        <select wire:model="rol"
                            class="block w-full pl-11 border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-2xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3 transition">
                            <option value="admin">Administrador General</option>
                            <option value="super_admin">Super Administrador</option>
                        </select>
                    </div>
                    @error('rol') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- SEGURIDAD CARD --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-200 dark:border-gray-800 p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-amber-600 dark:text-amber-400">
                    <x-heroicon-o-lock-closed class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Seguridad de la cuenta</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Password --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Contraseña {{ $userId ? '(Dejar en blanco para no cambiar)' : '' }}
                    </label>
                    <input type="password" wire:model="password"
                        class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-2xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3 transition">
                    @error('password') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Password Confirmation --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                        Confirmar Contraseña
                    </label>
                    <input type="password" wire:model="password_confirmation"
                        class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-2xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm px-4 py-3 transition">
                </div>
            </div>
        </div>

        {{-- FOOTER DE ACCIONES --}}
        <div class="flex items-center justify-between pt-6">
            <a href="{{ \App\Filament\Resources\Usuarios\UsuariosResource::getUrl('index') }}" 
                class="px-8 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-2xl hover:bg-gray-50 transition shadow-sm">
                Volver al listado
            </a>
            <button type="button" wire:click="save" 
                class="inline-flex items-center px-10 py-3 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-2xl shadow-xl transition gap-3">
                <x-heroicon-o-check-badge class="w-6 h-6" />
                {{ $userId ? 'Actualizar Información' : 'Crear Administrador' }}
            </button>
        </div>
    </div>
</x-filament-panels::page>
