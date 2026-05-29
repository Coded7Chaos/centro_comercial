<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\SuscripcionesTarifas;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class SimuladorAlquiler extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $title = 'Calculadora de Alquileres';
    protected static ?string $navigationLabel = 'Calculadora de Alquileres';
    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';
    public ?array $data = [];
    protected string $view = 'filament.pages.simulador-alquiler';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:SimuladorAlquiler') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Calculadora de Alquileres')
                    ->description('Ingrese los metros cuadrados y el tipo de suscripción para obtener una cotización automática según las tarifas vigentes.')
                    ->schema([
                        TextInput::make('tamano')
                            ->label('Tamaño del Local (m²)')
                            ->numeric()
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set) => $this->calcularCotizacion($set)),

                        Select::make('tipo')
                            ->label('Tipo de Suscripción')
                            ->options(SuscripcionesTarifas::tipos())
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $this->calcularCotizacion($set)),

                        TextInput::make('etiqueta')
                            ->label('Categoría detectada')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),

                        TextInput::make('cotizacion')
                            ->label('Precio Estimado')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('Bs.'),
                    ])->columns(2)
            ])
            ->statePath('data');
    }

    public function calcularCotizacion(Set $set): void
    {
        $tamano = $this->data['tamano'] ?? null;
        $tipo   = $this->data['tipo'] ?? null;

        $tarifa = SuscripcionesTarifas::precioPara(
            $tamano !== null && $tamano !== '' ? (float) $tamano : null,
            $tipo
        );

        if ($tarifa) {
            $set('cotizacion', number_format($tarifa->precio, 2, '.', ''));
            $set('etiqueta', $tarifa->etiqueta ?? '—');
        } else {
            $set('cotizacion', null);
            $set('etiqueta', 'Sin tarifa para esos parámetros');
        }
    }
    
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Calculadora de Alquileres';
    }
}
