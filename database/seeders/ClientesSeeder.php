<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'email'   => 'cliente@prueba.com',
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
            [
                'email'   => 'mateo.demo@mall.com',
                'nombres' => 'Mateo',
                'paterno' => 'Quispe',
                'materno' => 'Rojas',
                'cliente' => [
                    'ci'             => '7001002',
                    'numero_celular' => '71122334',
                    'genero'         => 'masculino',
                    'codigo_pais'    => '+591',
                ],
            ],
            [
                'email'   => 'sofia.demo@mall.com',
                'nombres' => 'Sofía',
                'paterno' => 'Suárez',
                'materno' => 'Aliaga',
                'cliente' => [
                    'ci'             => '7001003',
                    'numero_celular' => '72233445',
                    'genero'         => 'femenino',
                    'codigo_pais'    => '+591',
                ],
            ],
            [
                'email'   => 'diego.demo@mall.com',
                'nombres' => 'Diego',
                'paterno' => 'Choque',
                'materno' => 'Ortega',
                'cliente' => [
                    'ci'             => '7001004',
                    'numero_celular' => '73344556',
                    'genero'         => 'masculino',
                    'codigo_pais'    => '+591',
                ],
            ],
            [
                'email'   => 'camila.demo@mall.com',
                'nombres' => 'Camila',
                'paterno' => 'Mamani',
                'materno' => 'Vega',
                'cliente' => [
                    'ci'             => '7001005',
                    'numero_celular' => '74455667',
                    'genero'         => 'femenino',
                    'codigo_pais'    => '+591',
                ],
            ],
            [
                'email'   => 'joaquin.demo@mall.com',
                'nombres' => 'Joaquín',
                'paterno' => 'Flores',
                'paterno_materno' => 'Lima',
                'cliente' => [
                    'ci'             => '7001006',
                    'numero_celular' => '75566778',
                    'genero'         => 'masculino',
                    'codigo_pais'    => '+591',
                ],
            ],
        ];

        foreach ($clientes as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'nombres'           => $c['nombres'],
                    'apellido_paterno'  => $c['paterno'],
                    'apellido_materno'  => $c['materno'] ?? $c['paterno_materno'] ?? '',
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if (!$user->hasRole('cliente')) {
                $user->assignRole('cliente');
            }

            Clientes::firstOrCreate(
                ['user_id' => $user->id],
                $c['cliente']
            );
        }
    }
}
