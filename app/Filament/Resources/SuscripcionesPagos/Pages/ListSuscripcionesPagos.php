<?php

namespace App\Filament\Resources\SuscripcionesPagos\Pages;

use App\Filament\Resources\SuscripcionesPagos\SuscripcionesPagosResource;
use App\Filament\Widgets\PagosMonthNavigator;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use App\Models\PaymentSettings;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListSuscripcionesPagos extends ListRecords
{
    protected static string $resource = SuscripcionesPagosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('configuracion')
                ->label('Configuración')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->modalHeading('Configuración de Métodos de Pago')
                ->fillForm(fn() => PaymentSettings::first()?->toArray() ?? [])
                ->form([
                    TextInput::make('banco_nombre')
                        ->label('Banco')
                        ->required(),
                    TextInput::make('banco_nro_cuenta')
                        ->label('Número de Cuenta')
                        ->required(),
                    TextInput::make('banco_titular')
                        ->label('Titular de la Cuenta')
                        ->required(),
                    FileUpload::make('qr_imagen')
                        ->label('Imagen QR de Pago')
                        ->directory('config-pagos')
                        ->image()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $settings = PaymentSettings::first() ?? new PaymentSettings();
                    $settings->fill($data);
                    $settings->save();
                    
                    Notification::make()
                        ->title('Configuración guardada')
                        ->body('Los datos de transferencia y QR se actualizaron correctamente.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PagosMonthNavigator::class,
        ];
    }

    #[On('pagosNavChanged')]
    public function refreshOnNavChange(int $mes, int $anio): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(function ($query) {
                if ($this->activeTab === 'pendientes' || $this->activeTab === 'rechazados') {
                    return $query;
                }

                $mes = (int) session('pagos_nav_mes', now()->month);
                $anio = (int) session('pagos_nav_anio', now()->year);

                return $query
                    ->whereMonth('fecha_pago', $mes)
                    ->whereYear('fecha_pago', $anio);
            });
    }

    public function getTabs(): array
    {
        return [
            'verificados' => Tab::make('Pagos Confirmados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado_verificacion', 'verificado')->where('creado_por_admin', false)),
            'pendientes' => Tab::make('Solicitudes de Pago')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado_verificacion', 'pendiente')->where('creado_por_admin', false)),
            'rechazados' => Tab::make('Solicitudes Rechazadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado_verificacion', 'rechazado')->where('creado_por_admin', false)),
            'creados' => Tab::make('Pagos creados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('creado_por_admin', true)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'verificados';
    }
}
