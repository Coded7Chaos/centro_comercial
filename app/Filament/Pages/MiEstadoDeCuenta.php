<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\SuscripcionesCobros;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;

class MiEstadoDeCuenta extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Mi Estado de Cuenta';
    protected static ?string $title = 'Mi Estado de Cuenta';
    protected string $view = 'filament.pages.mi-estado-cuenta';

    public static function canAccess(): bool
    {
        // Solo visible para usuarios que tienen un perfil de Cliente asociado
        return Auth::check() && Auth::user()->cliente !== null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SuscripcionesCobros::query()
                    ->whereHas('suscripcion', function ($q) {
                        $q->where('cliente_id', Auth::user()->cliente->id);
                    })
            )
            ->columns([
                TextColumn::make('suscripcion.infraestructurasTienda.numero')
                    ->label('Tienda')
                    ->searchable(),
                TextColumn::make('concepto')
                    ->label('Concepto'),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->money('BOB'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'danger',
                        'pagado' => 'success',
                        'completado' => 'success',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                Action::make('descargar')
                    ->label('Ver PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (SuscripcionesCobros $record): string => route('cobros.pdf', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('fecha_vencimiento', 'desc');
    }
}
