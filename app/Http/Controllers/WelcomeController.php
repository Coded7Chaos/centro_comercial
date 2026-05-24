<?php

namespace App\Http\Controllers;

use App\Models\Infraestructuras;
use App\Models\User;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        $infraestructura = Infraestructuras::with([
            'pisosInfraestructura.tiendas.estado',
            'pisosInfraestructura.tiendas.marcas',
            'pisosInfraestructura.tiendas.cliente.user',
            'pisosInfraestructura.tiendas.productos.imagenes',
        ])->first();

        if (! $infraestructura) {
            return view('welcome', [
                'mall'             => null,
                'contacto'         => $this->contactoAdmin(),
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
            ->map(function ($piso, $pisoIndex) use ($palettes) {
                $stores = $piso->tiendas
                    ->sortBy('numero')
                    ->values()
                    ->map(function ($t) use ($palettes) {
                        $estadoLabel = $t->estado?->estado ?? 'Disponible';
                        $isAlquilada = strcasecmp($estadoLabel, 'Alquilada') === 0;

                        return [
                            'id'           => $t->id,
                            'numero'       => $t->numero,
                            'nombre'       => $t->nombre ?: ('Local ' . $t->numero),
                            'descripcion'  => $t->descripcion ?: 'Espacio comercial dentro del centro.',
                            'tamano'       => $t->tamano,
                            'telefono'     => $t->telefono_referencia,
                            'estado'       => $estadoLabel,
                            'is_alquilada' => $isAlquilada,
                            'marca'        => $isAlquilada
                                ? (
                                    $t->marcas->firstWhere('cliente_id', $t->cliente_id)?->nombre
                                    ?? $t->marcas->first()?->nombre
                                )
                                : null,
                            'inquilino'    => $isAlquilada && $t->cliente?->user
                                ? trim($t->cliente->user->nombres . ' ' . $t->cliente->user->apellido_paterno)
                                : null,
                            'productos'    => $isAlquilada
                                ? $t->productos->map(fn ($p) => [
                                    'id'       => $p->id,
                                    'nombre'   => $p->nombre,
                                    'precio'   => (float) $p->precio,
                                    'imagenes' => $p->imagenes->map(fn ($img) => ['url' => $img->url])->values(),
                                ])->values()
                                : [],
                            'accent' => $palettes[$t->id % count($palettes)],
                        ];
                    });

                return [
                    'level'        => $piso->id,
                    'displayLevel' => $pisoIndex + 1,
                    'name'         => $piso->nombre,
                    'vibe'         => $this->vibeDelPiso($piso->nombre),
                    'stores'       => $stores,
                ];
            });

        $mall = [
            'id'     => 'mall-' . $infraestructura->id,
            'name'   => $infraestructura->nombre,
            'city'   => $infraestructura->ubicacion ?? 'Distrito Central',
            'floors' => $floors,
        ];

        return view('welcome', [
            'mall'             => $mall,
            'contacto'         => $this->contactoAdmin(),
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
