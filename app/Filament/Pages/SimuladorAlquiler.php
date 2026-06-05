<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
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
        $this->form->fill([
            'duracion_valor' => 1,
            'duracion_unidad' => 'meses',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Calculadora de Alquileres')
                    ->description('Ingrese los metros cuadrados y la duración del contrato para obtener una cotización automática con descuento por tiempo según las tarifas vigentes.')
                    ->schema([
                        TextInput::make('tamano')
                            ->label('Tamaño del Local (m²)')
                            ->numeric()
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->calcularCotizacion()),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('duracion_valor')
                                    ->label('Duración del contrato')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn () => $this->calcularCotizacion()),

                                Select::make('duracion_unidad')
                                    ->label('Unidad')
                                    ->options([
                                        'meses' => 'Meses',
                                        'años' => 'Años',
                                    ])
                                    ->default('meses')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->calcularCotizacion()),
                            ]),

                        TextInput::make('pago_mensual_estimado')
                            ->label('Pago Mensual Estimado')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('Bs.')
                            ->placeholder('—'),

                        TextInput::make('garantia')
                            ->label('Garantía Inicial')
                            ->numeric()
                            ->prefix('Bs.')
                            ->placeholder('Se calcula igual al pago mensual')
                            ->helperText('Por defecto equivale al pago mensual. Puedes modificarla.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->recalcularTotal()),

                        TextInput::make('descuento_porcentaje')
                            ->label('Descuento por Tiempo')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('%')
                            ->placeholder('—'),

                        TextInput::make('etiqueta')
                            ->label('Categoría detectada')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),

                        TextInput::make('cotizacion')
                            ->label('Precio Estimado Total  (pago mensual × meses + garantía)')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('Bs.')
                            ->columnSpanFull(),
                    ])->columns(2)
            ])
            ->statePath('data');
    }

    public function calcularCotizacion(): void
    {
        $tamano        = $this->data['tamano'] ?? null;
        $duracionValor = $this->data['duracion_valor'] ?? null;
        $duracionUnidad = $this->data['duracion_unidad'] ?? 'meses';

        if ($tamano !== null && $tamano !== '' && $duracionValor !== null && $duracionValor !== '') {
            $meses = $duracionUnidad === 'años' ? (int) $duracionValor * 12 : (int) $duracionValor;
            $calc  = SuscripcionesTarifas::calcularAlquiler((float) $tamano, $meses);

            $pagoMensual = $calc['precio_mensual_con_descuento'];

            $this->data['etiqueta']             = $calc['etiqueta'];
            $this->data['descuento_porcentaje'] = number_format($calc['descuento_porcentaje'], 2, '.', '');
            $this->data['pago_mensual_estimado'] = number_format($pagoMensual, 2, '.', '');

            // Auto-fill/update warranty to equal the new estimated monthly payment
            $this->data['garantia'] = number_format($pagoMensual, 2, '.', '');

            $garantia = (float) ($this->data['garantia'] ?? $pagoMensual);
            $this->data['cotizacion'] = number_format($pagoMensual * $meses + $garantia, 2, '.', '');
        } else {
            $this->data['etiqueta']              = 'Ingrese tamaño y duración';
            $this->data['descuento_porcentaje']  = null;
            $this->data['pago_mensual_estimado'] = null;
            $this->data['garantia']              = null;
            $this->data['cotizacion']            = null;
        }
    }

    public function recalcularTotal(): void
    {
        $pagoMensual   = (float) ($this->data['pago_mensual_estimado'] ?? 0);
        $duracionValor = $this->data['duracion_valor'] ?? null;
        $duracionUnidad = $this->data['duracion_unidad'] ?? 'meses';

        if ($pagoMensual <= 0 || empty($duracionValor)) {
            return;
        }

        $meses    = $duracionUnidad === 'años' ? (int) $duracionValor * 12 : (int) $duracionValor;
        $garantia = (float) ($this->data['garantia'] ?? $pagoMensual);

        $this->data['cotizacion'] = number_format($pagoMensual * $meses + $garantia, 2, '.', '');
    }
    
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Calculadora de Alquileres';
    }
}
