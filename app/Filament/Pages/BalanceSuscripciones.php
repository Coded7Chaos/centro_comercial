<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Suscripciones;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;

class BalanceSuscripciones extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Auditoría Financiera';
    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:BalanceSuscripciones') ?? false;
    }
    protected string $view = 'filament.pages.balance-suscripciones';

    public function table(Table $table): Table
    {
        return $table
            ->query(Suscripciones::query()->with(['cliente.user', 'cobros.pagos', 'infraestructurasTienda']))
            ->columns([
                TextColumn::make('cliente.user.nombres')
                    ->label('Inquilino')
                    ->formatStateUsing(function ($state, Suscripciones $record) {
                        $u = $record->cliente?->user;
                        if (! $u) return $state ?: '—';
                        return trim($u->nombres . ' ' . $u->apellido_paterno);
                    })
                    ->searchable(['users.nombres', 'users.apellido_paterno'])
                    ->sortable(query: function ($query, string $direction) {
                        $query
                            ->leftJoin('clientes', 'clientes.id', '=', 'suscripciones.cliente_id')
                            ->leftJoin('users', 'users.id', '=', 'clientes.user_id')
                            ->orderBy('users.nombres', $direction)
                            ->select('suscripciones.*');
                    })
                    ->weight('bold'),
                TextColumn::make('infraestructurasTienda.numero')
                    ->label('Local')
                    ->badge()
                    ->color('info'),
                TextColumn::make('monto_acumulado_contratos')
                    ->label('Monto Acumulado de Contratos')
                    ->getStateUsing(function (Suscripciones $record) {
                        $query = Suscripciones::where('cliente_id', $record->cliente_id);
                        if ($record->infraestructuras_tienda_id) {
                            $query->where('infraestructuras_tienda_id', $record->infraestructuras_tienda_id);
                        } else {
                            $query->whereNull('infraestructuras_tienda_id');
                        }
                        $suscripciones = $query->with('cobros')->get();
                        
                        $total = 0;
                        foreach ($suscripciones as $sub) {
                            $total += $sub->cobros->sum('monto');
                        }
                        return $total;
                    })
                    ->money('BOB')
                    ->color('danger'),
                TextColumn::make('total_pagado')
                    ->label('Pagado')
                    ->getStateUsing(function (Suscripciones $record) {
                        $query = Suscripciones::where('cliente_id', $record->cliente_id);
                        if ($record->infraestructuras_tienda_id) {
                            $query->where('infraestructuras_tienda_id', $record->infraestructuras_tienda_id);
                        } else {
                            $query->whereNull('infraestructuras_tienda_id');
                        }
                        $suscripciones = $query->with('cobros.pagos')->get();
                        
                        $pagado = 0;
                        foreach ($suscripciones as $sub) {
                            foreach ($sub->cobros as $cobro) {
                                $pagado += $cobro->pagos->sum('monto_pagado');
                            }
                        }
                        return $pagado;
                    })
                    ->money('BOB')
                    ->color('success'),
                TextColumn::make('saldo_pendiente')
                    ->label('Saldo Restante')
                    ->getStateUsing(function (Suscripciones $record) {
                        $query = Suscripciones::where('cliente_id', $record->cliente_id);
                        if ($record->infraestructuras_tienda_id) {
                            $query->where('infraestructuras_tienda_id', $record->infraestructuras_tienda_id);
                        } else {
                            $query->whereNull('infraestructuras_tienda_id');
                        }
                        $suscripciones = $query->with('cobros.pagos')->get();

                        $deuda = 0;
                        $pagado = 0;
                        foreach ($suscripciones as $sub) {
                            foreach ($sub->cobros as $cobro) {
                                $deuda += $cobro->monto;
                                $pagado += $cobro->pagos->sum('monto_pagado');
                            }
                        }
                        return $deuda - $pagado;
                    })
                    ->money('BOB')
                    ->color('warning')
                    ->weight('bold'),
            ])
            ->recordActions([
                Action::make('reporte')
                    ->label('Ver Gráfica y Reporte')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->color('primary')
                    ->modalHeading(fn (Suscripciones $record) => 'Reporte Financiero: ' . ($record->cliente?->user
                            ? trim($record->cliente->user->nombres . ' ' . $record->cliente->user->apellido_paterno)
                            : ('Cliente #' . $record->cliente_id)))
                    ->modalContent(fn (Suscripciones $record) => view('filament.components.reporte-cliente-modal', [
                        'record' => $record,
                        'deuda' => Suscripciones::where('cliente_id', $record->cliente_id)
                            ->where('infraestructuras_tienda_id', $record->infraestructuras_tienda_id)
                            ->with('cobros')
                            ->get()
                            ->flatMap->cobros
                            ->sum('monto'),
                        'pagado' => \App\Models\SuscripcionesPagos::whereIn('suscripcion_cobro_id', function ($query) use ($record) {
                            $query->select('id')
                                ->from('suscripciones_cobros')
                                ->whereIn('suscripcion_id', Suscripciones::where('cliente_id', $record->cliente_id)
                                    ->where('infraestructuras_tienda_id', $record->infraestructuras_tienda_id)
                                    ->pluck('id'));
                        })->sum('monto_pagado')
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar Ventana')
            ]);
    }
    
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Auditoría Financiera por Contratos';
    }
}
