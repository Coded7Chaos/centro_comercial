<?php

namespace App\Filament\Resources\Categorias;

use App\Filament\Resources\Categorias\Pages\CreateCategorias;
use App\Filament\Resources\Categorias\Pages\EditCategorias;
use App\Filament\Resources\Categorias\Pages\ListCategorias;
use App\Filament\Resources\Categorias\Pages\ViewCategorias;
use App\Filament\Resources\Categorias\Schemas\CategoriasForm;
use App\Filament\Resources\Categorias\Schemas\CategoriasInfolist;
use App\Filament\Resources\Categorias\Tables\CategoriasTable;
use App\Models\Categorias;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoriasResource extends Resource
{
    protected static ?string $model = Categorias::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return CategoriasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoriasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategorias::route('/'),
            'create' => CreateCategorias::route('/create'),
            'view' => ViewCategorias::route('/{record}'),
            'edit' => EditCategorias::route('/{record}/edit'),
        ];
    }
}
