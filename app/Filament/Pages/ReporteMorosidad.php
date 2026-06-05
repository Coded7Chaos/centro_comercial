<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\SuscripcionesCobros;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Carbon;

class ReporteMorosidad extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Reporte de Morosidad';
    protected static ?string $title = 'Clientes en Mora';
    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';
    protected string $view = 'filament.pages.reporte-morosidad';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:ReporteMorosidad') ?? false;
    }

    protected static function baseQuery()
    {
        $query = SuscripcionesCobros::query()
            ->with(['pagos', 'suscripcion.cliente.user'])
            ->whereIn('estado', ['pendiente', 'parcial', 'vencido'])
            ->whereDate('fecha_vencimiento', '<', now()->toDateString());

        return \App\Support\ActiveInfraestructura::scopeQuery(
            $query, 'suscripcion.infraestructurasTienda.piso'
        );
    }

    protected function getViewData(): array
    {
        $cobros = static::baseQuery()->get();

        $totalMonto  = (float) $cobros->sum('monto');
        $totalPagado = (float) $cobros->sum(fn ($c) => $c->pagos->sum('monto_pagado'));
        $deuda       = max(0, $totalMonto - $totalPagado);

        $totalCobros   = $cobros->count();
        $totalClientes = $cobros
            ->pluck('suscripcion.cliente_id')
            ->filter()
            ->unique()
            ->count();

        return [
            'totalCobros'   => $totalCobros,
            'totalClientes' => $totalClientes,
            'deudaVencida'  => $deuda,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(static::baseQuery())
            ->columns([
                TextColumn::make('suscripcion.cliente.user.nombres')
                    ->label('Nombres')
                    ->placeholder('—')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('suscripcion.cliente.user', function ($q) use ($search) {
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_materno)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
                        });
                    })
                    ->sortable(query: function ($query, string $direction) {
                        $query
                            ->leftJoin('suscripciones', 'suscripciones.id', '=', 'suscripciones_cobros.suscripcion_id')
                            ->leftJoin('clientes', 'clientes.id', '=', 'suscripciones.cliente_id')
                            ->leftJoin('users', 'users.id', '=', 'clientes.user_id')
                            ->orderBy('users.nombres', $direction)
                            ->select('suscripciones_cobros.*');
                    }),
                TextColumn::make('suscripcion.cliente.user.apellido_paterno')
                    ->label('Apellidos')
                    ->placeholder('—')
                    ->formatStateUsing(function ($state, SuscripcionesCobros $r) {
                        $u = $r->suscripcion?->cliente?->user;
                        if (! $u) return '—';
                        return trim(($u->apellido_paterno ?? '') . ' ' . ($u->apellido_materno ?? '')) ?: '—';
                    })
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('suscripcion.cliente.user', function ($q) use ($search) {
                            $q->where(function ($sq) use ($search) {
                                $sq->whereRaw("unaccent(lower(nombres)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_paterno)) ILIKE unaccent(lower(?))", ["%{$search}%"])
                                  ->orWhereRaw("unaccent(lower(apellido_materno)) ILIKE unaccent(lower(?))", ["%{$search}%"]);
                            });
                        });
                    }),
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->wrap(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->money('BOB'),
                TextColumn::make('pagado')
                    ->label('Pagado')
                    ->state(fn (SuscripcionesCobros $r) => (float) $r->pagos->sum('monto_pagado'))
                    ->money('BOB')
                    ->color('success'),
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->state(fn (SuscripcionesCobros $r) => max(0, ((float) $r->monto) - ((float) $r->pagos->sum('monto_pagado'))))
                    ->money('BOB')
                    ->weight('bold')
                    ->color('danger'),
                TextColumn::make('fecha_vencimiento')
                    ->label('Venció el')
                    ->date()
                    ->color('danger'),
                TextColumn::make('dias_atraso')
                    ->label('Días de atraso')
                    ->state(function (SuscripcionesCobros $r) {
                        if (! $r->fecha_vencimiento) {
                            return null;
                        }
                        return Carbon::parse($r->fecha_vencimiento)
                            ->startOfDay()
                            ->diffInDays(Carbon::now()->startOfDay());
                    })
                    ->badge()
                    ->color(fn ($state) => $state === null ? 'gray' : ($state > 30 ? 'danger' : ($state > 7 ? 'warning' : 'gray')))
                    ->suffix(' días'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn ($state) => match ($state) {
                        'vencido' => 'danger',
                        'parcial' => 'warning',
                        default   => 'gray',
                    }),
            ])
            ->defaultSort('fecha_vencimiento', 'asc');
    }
}
