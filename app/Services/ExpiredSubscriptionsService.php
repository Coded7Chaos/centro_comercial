<?php

namespace App\Services;

use App\Models\Categorias;
use App\Models\Clientes;
use App\Models\EstadoTienda;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use App\Models\Productos;
use App\Models\Suscripciones;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpiredSubscriptionsService
{
    public function process(?string $today = null): int
    {
        $today ??= now()->toDateString();

        $storeIds = InfraestructurasTiendas::query()->pluck('id');

        $expired = Suscripciones::query()
            ->whereDate('fecha_fin', '<', $today)
            ->get(['cliente_id', 'infraestructuras_tienda_id']);

        $released = 0;

        DB::transaction(function () use ($expired, $storeIds, $today, &$released) {
            $storeIds->each(function (int $tiendaId) use ($today, &$released) {
                if ($this->syncStoreForToday($tiendaId, $today)) {
                    $released++;
                }
            });

            if ($expired->isNotEmpty()) {
                $expired
                    ->pluck('infraestructuras_tienda_id')
                    ->filter()
                    ->unique()
                    ->diff($storeIds)
                    ->each(function (int $tiendaId) use ($today, &$released) {
                        if ($this->releaseStoreIfExpired($tiendaId, $today)) {
                            $released++;
                        }
                    });

                $expired
                    ->pluck('cliente_id')
                    ->filter()
                    ->unique()
                    ->each(fn (int $clienteId) => $this->blockClientIfWithoutCurrentOrFutureContracts($clienteId, $today));
            }
        });

        return $released;
    }

    public function syncStoreForToday(int $tiendaId, ?string $today = null): bool
    {
        $today ??= now()->toDateString();

        $suscripcionActiva = Suscripciones::query()
            ->where('infraestructuras_tienda_id', $tiendaId)
            ->whereDate('fecha_inicio', '<=', $today)
            ->whereDate('fecha_fin', '>=', $today)
            ->latest('fecha_inicio')
            ->first();

        if (! $suscripcionActiva) {
            return $this->releaseStoreIfExpired($tiendaId, $today);
        }

        $tienda = InfraestructurasTiendas::find($tiendaId);

        if (! $tienda) {
            return false;
        }

        $estadoAlquilada = EstadoTienda::where('estado', 'Alquilada')->first();

        $updates = [
            'cliente_id' => $suscripcionActiva->cliente_id,
        ];

        if ($estadoAlquilada) {
            $updates['id_estado'] = $estadoAlquilada->id;
        }

        $tienda->update($updates);

        if ($suscripcionActiva->marca_id) {
            $tienda->marcas()->sync([$suscripcionActiva->marca_id]);
        } else {
            $tienda->marcas()->detach();
        }

        return false;
    }

    public function releaseStoreIfExpired(int $tiendaId, ?string $today = null): bool
    {
        $today ??= now()->toDateString();

        $hasActiveContract = Suscripciones::query()
            ->where('infraestructuras_tienda_id', $tiendaId)
            ->whereDate('fecha_inicio', '<=', $today)
            ->whereDate('fecha_fin', '>=', $today)
            ->exists();

        if ($hasActiveContract) {
            return false;
        }

        $tienda = InfraestructurasTiendas::with(['productos.imagenes', 'marcas'])->find($tiendaId);

        if (! $tienda) {
            return false;
        }

        $shouldCleanCommercialData = $tienda->cliente_id !== null
            || $tienda->marcas->isNotEmpty()
            || $tienda->productos->isNotEmpty();

        $estadoDisponible = EstadoTienda::where('estado', 'Disponible')->first();

        $updates = [
            'cliente_id' => null,
        ];

        if ($shouldCleanCommercialData) {
            $this->deleteStoreProducts($tienda->productos);

            $tienda->marcas()->detach();

            $updates = array_merge($updates, [
                'nombre' => null,
                'descripcion' => null,
                'telefono_referencia' => null,
                'vitrina_1' => null,
                'vitrina_2' => null,
                'vitrina_3' => null,
            ]);
        }

        if ($estadoDisponible) {
            $updates['id_estado'] = $estadoDisponible->id;
        }

        $tienda->update($updates);

        return $shouldCleanCommercialData;
    }

    public function blockClientIfWithoutCurrentOrFutureContracts(int $clienteId, ?string $today = null): bool
    {
        $today ??= now()->toDateString();

        $hasCurrentOrFutureContracts = Suscripciones::query()
            ->where('cliente_id', $clienteId)
            ->whereDate('fecha_fin', '>=', $today)
            ->exists();

        if ($hasCurrentOrFutureContracts) {
            return false;
        }

        $cliente = Clientes::withTrashed()->with('user')->find($clienteId);

        if (! $cliente) {
            return false;
        }

        $this->deletePrivateBrands($cliente);
        $this->deletePrivateCategories($cliente);

        if (! $cliente->trashed()) {
            $cliente->delete();
        } elseif ($cliente->user && ! $cliente->user->trashed()) {
            $cliente->user->delete();
        }

        return true;
    }

    private function deleteStoreProducts(Collection $productos): void
    {
        $productos->each(function (Productos $producto) {
            $producto->imagenes->each(function ($imagen) {
                if ($imagen->url && ! str_starts_with($imagen->url, 'http')) {
                    Storage::disk('public')->delete($imagen->url);
                }

                $imagen->delete();
            });

            $producto->delete();
        });
    }

    private function deletePrivateBrands(Clientes $cliente): void
    {
        Marcas::withTrashed()
            ->where('cliente_id', $cliente->id)
            ->get()
            ->each(function (Marcas $marca) {
                $marca->tiendas()->detach();

                if (! $marca->trashed()) {
                    $marca->delete();
                }
            });
    }

    private function deletePrivateCategories(Clientes $cliente): void
    {
        Categorias::query()
            ->where('cliente_id', $cliente->id)
            ->whereNotNull('categoria_padre_id')
            ->delete();

        Categorias::query()
            ->where('cliente_id', $cliente->id)
            ->whereNull('categoria_padre_id')
            ->delete();
    }
}
