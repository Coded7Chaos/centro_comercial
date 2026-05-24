<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
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
            [
                'email'   => 'cliente@prueba.com',
                'rol'     => 'cliente',
                'nombres' => 'Cliente',
                'paterno' => 'Prueba',
                'materno' => 'Test',
                'cliente' => [
                    'ci'             => '11223344',
                    'numero_celular' => '76543210',
                    'genero'         => 'masculino',
                    'codigo_pais'    => '+591',
                ],
            ],
        ];

        foreach ($usuarios as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'nombres'           => $u['nombres'],
                    'apellido_paterno'  => $u['paterno'],
                    'apellido_materno'  => $u['materno'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($u['rol'])) {
                $user->assignRole($u['rol']);
            }

            if (! empty($u['cliente'])) {
                Clientes::firstOrCreate(
                    ['user_id' => $user->id],
                    $u['cliente']
                );
            }
        }
    }
}
