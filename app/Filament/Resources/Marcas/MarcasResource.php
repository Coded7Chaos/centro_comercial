<?php

namespace App\Filament\Resources\Marcas;

use App\Filament\Resources\Marcas\Pages\CreateMarcas;
use App\Filament\Resources\Marcas\Pages\EditMarcas;
use App\Filament\Resources\Marcas\Pages\ListMarcas;
use App\Filament\Resources\Marcas\Pages\ViewMarcas;
use App\Filament\Resources\Marcas\Schemas\MarcasForm;
use App\Filament\Resources\Marcas\Schemas\MarcasInfolist;
use App\Filament\Resources\Marcas\Tables\MarcasTable;
use App\Models\Marcas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarcasResource extends Resource
{
    protected static ?string $model = Marcas::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return MarcasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MarcasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarcasTable::configure($table);
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
            'index' => ListMarcas::route('/'),
            'create' => CreateMarcas::route('/create'),
            'view' => ViewMarcas::route('/{record}'),
            'edit' => EditMarcas::route('/{record}/edit'),
        ];
    }
}
