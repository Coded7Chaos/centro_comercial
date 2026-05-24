<?php

namespace App\Filament\Resources\Productos;

use App\Filament\Resources\Productos\Pages\CreateProductos;
use App\Filament\Resources\Productos\Pages\EditProductos;
use App\Filament\Resources\Productos\Pages\ListProductos;
use App\Filament\Resources\Productos\Pages\ViewProductos;
use App\Filament\Resources\Productos\Schemas\ProductosForm;
use App\Filament\Resources\Productos\Schemas\ProductosInfolist;
use App\Filament\Resources\Productos\Tables\ProductosTable;
use App\Models\Productos;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class ProductosResource extends Resource
{
    protected static ?string $model = Productos::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ProductosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductos::route('/'),
            'create' => CreateProductos::route('/create'),
            'view' => ViewProductos::route('/{record}'),
            'edit' => EditProductos::route('/{record}/edit'),
        ];
    }

    // =========================
    // VALIDACIÓN CREATE
    // =========================

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        self::validarImagenes($data);

        return $data;
    }

    // =========================
    // VALIDACIÓN EDIT
    // =========================

    public static function mutateFormDataBeforeSave(array $data): array
    {
        self::validarImagenes($data);

        return $data;
    }

    // =========================
    // VALIDACIÓN CENTRAL
    // =========================

    protected static function validarImagenes(array $data): void
    {
        $imagenes = collect($data['imagenes'] ?? []);

        $principales = $imagenes
            ->where('tipo', 'principal')
            ->count();

        if ($principales < 1) {
            throw ValidationException::withMessages([
                'imagenes' => 'Debe existir una imagen principal.',
            ]);
        }

        if ($principales > 1) {
            throw ValidationException::withMessages([
                'imagenes' => 'Solo puede existir una imagen principal.',
            ]);
        }

        if ($imagenes->count() > 6) {
            throw ValidationException::withMessages([
                'imagenes' => 'Máximo 6 imágenes permitidas.',
            ]);
        }
    }
}