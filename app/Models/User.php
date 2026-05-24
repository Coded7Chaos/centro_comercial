<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements MustVerifyEmail, HasName, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombres', 'apellido_paterno', 'apellido_materno', 'email'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('usuarios');
    }

    public function getFilamentName(): string
    {
        $nombre = explode(' ', trim($this->nombres))[0];
        return $nombre;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Si es super_admin, tiene acceso total.
        // Shield se encargará de los permisos granulares en el resto del panel.
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Para otros roles, podrías querer restringir por panel o simplemente dejar que Shield maneje el resto.
        // Por ahora, permitimos el acceso si tienen cualquier rol que no sea 'cliente'
        // (o simplemente si tienen acceso al panel configurado en Shield)
        return $this->hasRole(['admin', 'panel_user']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cliente()
    {
        return $this->hasOne(Clientes::class);
    }
}
