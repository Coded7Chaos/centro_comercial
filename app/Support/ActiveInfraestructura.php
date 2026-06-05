<?php

namespace App\Support;

use App\Models\Infraestructuras;

class ActiveInfraestructura
{
    const SESSION_KEY   = 'active_infraestructura_id';
    const DASHBOARD_KEY = 'dashboard_infra_filter';

    public static function getId(): ?int
    {
        $id = session(self::SESSION_KEY);
        return $id ? (int) $id : null;
    }

    public static function setId(?int $id): void
    {
        session([self::SESSION_KEY => $id]);
    }

    public static function isSet(): bool
    {
        return session()->has(self::SESSION_KEY) && session(self::SESSION_KEY) !== null;
    }

    public static function get(): ?Infraestructuras
    {
        $id = self::getId();
        return $id ? Infraestructuras::find($id) : null;
    }

    /** Dashboard-specific filter (null = show all). */
    public static function getDashboardId(): ?int
    {
        $val = session(self::DASHBOARD_KEY);
        return ($val === null || $val === 'all') ? null : (int) $val;
    }

    public static function setDashboardId(?int $id): void
    {
        session([self::DASHBOARD_KEY => $id]);
    }

    /**
     * Apply infra scope to an Eloquent query via a dot-separated relation chain.
     * e.g. scopeQuery($query, 'infraestructurasTienda.piso')
     */
    public static function scopeQuery(\Illuminate\Database\Eloquent\Builder $query, string $relationChain): \Illuminate\Database\Eloquent\Builder
    {
        $id = self::getId();
        if (!$id) return $query;

        return $query->whereHas($relationChain, fn ($q) => $q->where('infraestructura_id', $id));
    }

    /** Same but using the dashboard filter. */
    public static function scopeQueryDashboard(\Illuminate\Database\Eloquent\Builder $query, string $relationChain): \Illuminate\Database\Eloquent\Builder
    {
        $id = self::getDashboardId();
        if (!$id) return $query;

        return $query->whereHas($relationChain, fn ($q) => $q->where('infraestructura_id', $id));
    }
}
