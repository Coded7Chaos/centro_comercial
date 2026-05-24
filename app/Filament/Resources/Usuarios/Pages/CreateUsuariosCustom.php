<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuariosResource;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class CreateUsuariosCustom extends Page
{
    protected static string $resource = UsuariosResource::class;

    protected string $view = 'filament.resources.usuarios.pages.form-usuarios-custom';

    protected static ?string $title = 'Crear nuevo administrador';

    public $userId = null;
    public $nombres = '';
    public $apellido_paterno = '';
    public $apellido_materno = '';
    public $email = '';
    public $rol = 'admin';
    public $password = '';
    public $password_confirmation = '';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('Create:User') ?? false;
    }

    public function save()
    {
        $this->validate([
            'nombres' => 'required|min:2',
            'apellido_paterno' => 'required',
            'apellido_materno' => 'required',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:admin,super_admin',
            'password' => 'required|min:8|confirmed',
        ]);

        $actor = auth()->user();
        $rolesPermitidos = $actor?->hasRole('super_admin')
            ? ['admin', 'super_admin']
            : [];

        if (! in_array($this->rol, $rolesPermitidos, true)) {
            Notification::make()
                ->title('No tienes permiso para asignar ese rol')
                ->danger()
                ->send();
            return;
        }

        try {
            $user = User::create([
                'nombres' => $this->nombres,
                'apellido_paterno' => $this->apellido_paterno,
                'apellido_materno' => $this->apellido_materno,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $user->assignRole($this->rol);

            Notification::make()
                ->title('Administrador creado exitosamente')
                ->success()
                ->send();

            return redirect()->to(UsuariosResource::getUrl('index'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
