<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministradoresSeeder extends Seeder
{
    public function run(): void
    {
        $administradores = [
            [
                'email'   => 'superadmin@admin.com',
                'rol'     => 'super_admin',
                'nombres' => 'Super',
                'paterno' => 'Admin',
                'materno' => 'General',
            ],
            [
                'email'   => 'admin@admin.com',
                'rol'     => 'admin',
                'nombres' => 'Admin',
                'paterno' => 'General',
                'materno' => 'Mall',
            ],
        ];

        foreach ($administradores as $admin) {
            $user = User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'nombres'           => $admin['nombres'],
                    'apellido_paterno'  => $admin['paterno'],
                    'apellido_materno'  => $admin['materno'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if (!$user->hasRole($admin['rol'])) {
                $user->assignRole($admin['rol']);
            }
        }
    }
}
