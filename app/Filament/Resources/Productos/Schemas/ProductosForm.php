<?php

namespace App\Filament\Resources\Productos\Schemas;

use App\Models\Categorias;
use App\Models\Clientes;
use App\Models\InfraestructurasTiendas;
use App\Models\Marcas;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // =========================
            // NOMBRE
            // =========================

            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(80)

                ->regex('/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\.\#]+$/u')

                ->validationMessages([
                    'required' => 'El nombre es obligatorio.',
                    'max_length' => 'Máximo 80 caracteres.',
                    'regex' => 'El nombre debe contener letras y puede incluir números.',
                ]),

            // =========================
            // PRECIO
            // =========================

            TextInput::make('precio')
                ->label('Precio')
                ->required()

                ->prefix('Bs.')

                ->placeholder('0,00')

                // límite visual
                ->maxLength(14)

                // permite teclado decimal
                ->inputMode('decimal')

                // FORMATO VISUAL
                ->formatStateUsing(function ($state) {

                    if (! $state) {
                        return null;
                    }

                    // convertir a formato boliviano
                    return number_format((float) $state, 2, ',', '.');
                })

                // VALIDACIÓN
                ->rule('regex:/^\d{1,3}(\.\d{3})*(,\d{1,2})?$|^\d+(,\d{1,2})?$/')

                ->validationMessages([
                    'required' => 'El precio es obligatorio.',

                    'regex' => 'Formato válido: 1.000,50',

                    'max_length' => 'El precio es demasiado largo.',
                ])

                // LIMPIAR ANTES DE GUARDAR
                ->dehydrateStateUsing(function ($state) {

                    if (! $state) {
                        return null;
                    }

                    // quitar puntos
                    $state = str_replace('.', '', $state);

                    // convertir coma decimal
                    $state = str_replace(',', '.', $state);

                    return $state;
                }),

            // =========================
            // DESCRIPCIÓN
            // =========================

            Textarea::make('descripcion')
                ->label('Descripción')

                ->rows(4)

                ->columnSpanFull()

                ->maxLength(1000)

                ->regex('/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ]).+$/u')

                ->validationMessages([
                    'max_length' => 'Máximo 1000 caracteres.',
                    'regex' => 'La descripción debe contener letras.',
                ]),

            // CLIENTE

            Select::make('cliente_temp')
                ->label('Cliente')
                ->options(
                    fn () => Clientes::with('user')->get()
                        ->mapWithKeys(function ($cliente) {
                            $nombre = $cliente->nombre_completo;

                            return [$cliente->id => "{$cliente->id} - {$nombre}"];
                        })
                )
                ->disabled()
                ->dehydrated(false),

            // TIENDA

            Select::make('infraestructuras_tienda_id')
                ->label('Tienda')
                ->options(
                    fn () => InfraestructurasTiendas::with(['cliente.user', 'piso.infraestructura'])
                        ->whereHas('estado', fn ($query) => $query->where('estado', 'Alquilada'))
                        ->orderBy('numero')
                        ->get()
                        ->mapWithKeys(function ($tienda) {
                            $cliente = $tienda->cliente?->nombre_completo ?? 'Sin cliente';
                            $ubicacion = trim(($tienda->piso?->infraestructura?->nombre ?? '').' '.$tienda->piso?->nombre);
                            $nombre = $tienda->nombre ?: "Tienda #{$tienda->numero}";

                            return [$tienda->id => "{$nombre} - {$cliente}".($ubicacion ? " ({$ubicacion})" : '')];
                        })
                )

                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    $clienteId = $state
                        ? InfraestructurasTiendas::whereKey($state)->value('cliente_id')
                        : null;

                    $set('cliente_temp', $clienteId);
                }),

            // =========================
            // CATEGORÍA
            // =========================

            Select::make('categoria_id')
                ->label('Categoría')

                ->options(
                    Categorias::whereNull('categoria_padre_id')
                        ->pluck('nombre', 'id')
                )

                ->live()

                ->required()

                ->validationMessages([
                    'required' => 'Debes seleccionar una categoría.',
                ])

                ->afterStateUpdated(
                    fn ($set) => $set('subcategoria_id', null)
                ),

            // =========================
            // SUBCATEGORÍA
            // =========================

            Select::make('subcategoria_id')
                ->label('Subcategoría')

                ->options(
                    fn ($get) => $get('categoria_id')

                        ? Categorias::where(
                            'categoria_padre_id',
                            $get('categoria_id')
                        )->pluck('nombre', 'id')

                        : []
                )

                ->required()

                ->validationMessages([
                    'required' => 'Debes seleccionar una subcategoría.',
                ]),

            // =========================
            // MARCA
            // =========================

            Select::make('marca_id')
                ->label('Marca')
                ->options(
                    fn () => Marcas::with('cliente.user')
                        ->orderBy('nombre')
                        ->get()
                        ->mapWithKeys(function ($marca) {
                            $origen = $marca->cliente
                                ? 'Privada - '.$marca->cliente->nombre_completo
                                : 'Pública';

                            return [$marca->id => "{$marca->nombre} ({$origen})"];
                        })
                )
                ->required()
                ->validationMessages([
                    'required' => 'Debes seleccionar una marca.',
                ]),

            // =========================
            // IMÁGENES
            // =========================

            Repeater::make('imagenes')

                ->label('Imágenes')

                ->columnSpanFull()

                ->defaultItems(1)

                ->minItems(1)

                ->maxItems(6)

                ->reorderable(false)

                ->deletable(function ($state) {

                    return count($state ?? []) > 1;
                })

                ->validationMessages([
                    'min_items' => 'Debes agregar al menos una imagen.',
                    'max_items' => 'Máximo 6 imágenes.',
                ])

                ->schema([

                    FileUpload::make('url')

                        ->label('Imagen')

                        ->image()

                        ->disk('public')

                        ->directory('productos')

                        ->fetchFileInformation(false)

                        ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                            return [
                                'name' => basename($file),
                                'size' => 0,
                                'type' => null,
                                'url' => str_starts_with($file, 'http')
                                    ? $file
                                    : asset('storage/'.$file),
                            ];
                        })

                        ->required()

                        ->validationMessages([
                            'required' => 'La imagen es obligatoria.',
                        ]),

                ]),
        ]);
    }
}
