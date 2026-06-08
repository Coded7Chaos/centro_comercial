<?php

namespace App\Http\Controllers;

use App\Models\Infraestructuras;
use App\Models\User;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        $infraestructuraId = request('infraestructura_id');

        $query = Infraestructuras::with([
            'pisosInfraestructura.tiendas.estado',
            'pisosInfraestructura.tiendas.marcas',
            'pisosInfraestructura.tiendas.cliente.user',
            'pisosInfraestructura.tiendas.productos.imagenes',
        ]);

        if ($infraestructuraId) {
            $infraestructura = $query->find($infraestructuraId);
        }

        if (!isset($infraestructura) || !$infraestructura) {
            $infraestructura = $query->first();
        }

        $contactoAdmin = $this->contactoAdmin();

        if (! $infraestructura) {
            $mall = [
                'id'     => 'mall-none',
                'name'   => 'Mall Centro Comercial',
                'city'   => 'Distrito Central',
                'floors' => [
                    [
                        'level' => 1,
                        'displayLevel' => 1,
                        'name' => 'Planta Baja',
                        'vibe' => 'Comercio & Servicios',
                        'imagen_fondo' => '/images/backgrounds/bg_mall_white.jpg',
                        'stores' => [
                            [
                                'id' => 1,
                                'numero' => 101,
                                'nombre' => 'Local Comercial',
                                'descripcion' => 'Espacio comercial disponible.',
                                'tamano' => '10.00',
                                'telefono' => $contactoAdmin['telefono'],
                                'email_contacto' => $contactoAdmin['email'],
                                'estado' => 'Disponible',
                                'is_alquilada' => false,
                                'marca' => null,
                                'marca_logo' => null,
                                'inquilino' => null,
                                'vitrina_1' => null,
                                'vitrina_2' => null,
                                'vitrina_3' => null,
                                'productos' => [],
                                'accent' => 'graphite',
                            ]
                        ]
                    ]
                ],
            ];
            return view('welcome', [
                'mall'             => $mall,
                'contacto'         => $contactoAdmin,
                'suscripcionesUrl' => route('suscripciones'),
            ]);
        }

        $palettes = [
            'graphite', 'steel', 'platinum', 'chrome', 'champagne', 'brass',
            'obsidian', 'pearl', 'copper', 'gunmetal', 'silk', 'jade',
        ];

        $floors = $infraestructura->pisosInfraestructura
            ->sortBy('id')
            ->values()
            ->map(function ($piso, $pisoIndex) use ($palettes, $contactoAdmin) {
                $stores = $piso->tiendas
                    ->sortBy('numero')
                    ->values()
                    ->map(function ($t) use ($palettes, $contactoAdmin) {
                        $estadoLabel = $t->estado?->estado ?? 'Disponible';
                        $isAlquilada = strcasecmp($estadoLabel, 'Alquilada') === 0;

                        return [
                            'id'           => $t->id,
                            'numero'       => $t->numero,
                            'nombre'       => $t->nombre ?: ('Local ' . $t->numero),
                            'descripcion'  => $t->descripcion ?: 'Espacio comercial dentro del centro.',
                            'tamano'       => $t->tamano,
                            'telefono'     => $t->telefono_referencia ?: $contactoAdmin['telefono'],
                            'email_contacto' => $t->email_contacto ?: $contactoAdmin['email'],
                            'estado'       => $estadoLabel,
                            'is_alquilada' => $isAlquilada,
                            'marca'        => $isAlquilada
                                ? (
                                    $t->marcas->firstWhere('cliente_id', $t->cliente_id)?->nombre
                                    ?? $t->marcas->first()?->nombre
                                )
                                : null,
                            'marca_logo'   => $isAlquilada
                                ? (
                                    ($brand = $t->marcas->firstWhere('cliente_id', $t->cliente_id) ?? $t->marcas->first())
                                    ? ($brand->logo ? \Illuminate\Support\Facades\Storage::url($brand->logo) : null)
                                    : null
                                )
                                : null,
                            'inquilino'    => $isAlquilada && $t->cliente?->user
                                ? trim($t->cliente->user->nombres . ' ' . $t->cliente->user->apellido_paterno)
                                : null,
                            'vitrina_1'    => $t->vitrina_1
                                ? (str_starts_with($t->vitrina_1, 'http') ? $t->vitrina_1 : \Illuminate\Support\Facades\Storage::url($t->vitrina_1))
                                : null,
                            'vitrina_2'    => $t->vitrina_2
                                ? (str_starts_with($t->vitrina_2, 'http') ? $t->vitrina_2 : \Illuminate\Support\Facades\Storage::url($t->vitrina_2))
                                : null,
                            'vitrina_3'    => $t->vitrina_3
                                ? (str_starts_with($t->vitrina_3, 'http') ? $t->vitrina_3 : \Illuminate\Support\Facades\Storage::url($t->vitrina_3))
                                : null,
                            'productos'    => $isAlquilada
                                ? $t->productos->map(fn ($p) => [
                                    'id'       => $p->id,
                                    'nombre'   => $p->nombre,
                                    'precio'   => (float) $p->precio,
                                    'imagenes' => $p->imagenes->map(fn ($img) => ['url' => str_starts_with($img->url, 'http') ? $img->url : \Illuminate\Support\Facades\Storage::url($img->url)])->values(),
                                ])->values()
                                : [],
                            'accent' => $palettes[$t->id % count($palettes)],
                        ];
                    });

                return [
                    'level'        => $piso->id,
                    'displayLevel' => $piso->numero ?: ($pisoIndex + 1),
                    'name'         => $piso->nombre,
                    'vibe'         => $this->vibeDelPiso($piso->nombre),
                    'imagen_fondo' => $piso->imagen_fondo ?: '/images/backgrounds/bg_mall_white.jpg',
                    'stores'       => $stores,
                ];
            });

        if ($floors->isEmpty()) {
            $floors = collect([[
                'level'        => 0,
                'displayLevel' => 'PB',
                'name'         => 'Sin pisos configurados',
                'vibe'         => 'Infraestructura recién creada',
                'imagen_fondo' => '/images/backgrounds/bg_mall_white.jpg',
                'stores'       => [[
                    'id'           => 0,
                    'numero'       => 1,
                    'nombre'       => 'Sin tiendas aún',
                    'descripcion'  => 'Esta infraestructura aún no tiene pisos ni tiendas. Configúrala desde el panel de administración.',
                    'tamano'       => null,
                    'telefono'     => $contactoAdmin['telefono'],
                    'email_contacto' => $contactoAdmin['email'],
                    'estado'       => 'Disponible',
                    'is_alquilada' => false,
                    'marca'        => null,
                    'marca_logo'   => null,
                    'inquilino'    => null,
                    'vitrina_1'    => null,
                    'vitrina_2'    => null,
                    'vitrina_3'    => null,
                    'productos'    => [],
                    'accent'       => 'graphite',
                ]],
            ]]);
        }

        $mall = [
            'id'     => 'mall-' . $infraestructura->id,
            'name'   => $infraestructura->nombre,
            'city'   => $infraestructura->ubicacion ?? 'Distrito Central',
            'floors' => $floors,
        ];

        if (request()->wantsJson() || request()->ajax() || request()->has('json')) {
            return response()->json([
                'mall' => $mall,
            ]);
        }

        return view('welcome', [
            'mall'             => $mall,
            'contacto'         => $contactoAdmin,
            'suscripcionesUrl' => route('suscripciones'),
        ]);
    }

    private function contactoAdmin(): array
    {
        $admin = User::role(['super_admin', 'admin'])
            ->with('cliente')
            ->orderBy('id')
            ->first();

        return [
            'nombre'   => $admin
                ? trim($admin->nombres . ' ' . $admin->apellido_paterno)
                : 'Administración Mall',
            'email'    => $admin?->email ?? 'contacto@mallgranvia.com',
            'telefono' => $admin?->cliente?->numero_celular ?? '+591 7000 0000',
        ];
    }

    private function vibeDelPiso(?string $nombre): string
    {
        $n = strtolower((string) $nombre);
        return match (true) {
            str_contains($n, 'tech')   || str_contains($n, 'plaza')  => 'Tecnología & Gadgets',
            str_contains($n, 'sky')    || str_contains($n, 'lounge') => 'Gastronomía & Entretenimiento',
            str_contains($n, 'fashion')                              => 'Moda & Estilo',
            str_contains($n, 'lobby')  || str_contains($n, 'grand')  => 'Servicios & Cultura',
            default                                                  => 'Comercio & Servicios',
        };
    }
}
