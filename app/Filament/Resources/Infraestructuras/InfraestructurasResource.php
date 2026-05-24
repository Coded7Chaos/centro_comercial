<?php

namespace App\Filament\Resources\Infraestructuras;

use App\Filament\Resources\Infraestructuras\Pages\CreateInfraestructurasCustom;
use App\Filament\Resources\Infraestructuras\Pages\EditInfraestructurasCustom;
use App\Filament\Resources\Infraestructuras\Pages\ListInfraestructurasCustom;
use App\Filament\Resources\Infraestructuras\Pages\InfraestructurasPisosDiseno;
use App\Filament\Resources\Infraestructuras\Pages\ListInfraestructuras;
use App\Filament\Resources\Infraestructuras\Pages\TiendasPropietarios;
use App\Filament\Resources\Infraestructuras\Pages\ViewInfraestructuras;
use App\Filament\Resources\Infraestructuras\Schemas\InfraestructurasForm;
use App\Filament\Resources\Infraestructuras\Schemas\InfraestructurasInfolist;
use App\Filament\Resources\Infraestructuras\Tables\InfraestructurasTable;
use App\Models\Infraestructuras;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InfraestructurasResource extends Resource
{
    protected static ?string $model = Infraestructuras::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Infraestructura';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return InfraestructurasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InfraestructurasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfraestructurasTable::configure($table);
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
            'index' => ListInfraestructurasCustom::route('/'),
            'create' => CreateInfraestructurasCustom::route('/create'),
            'view' => ViewInfraestructuras::route('/{record}'),
            'edit' => EditInfraestructurasCustom::route('/{record}/edit'),
            'diseno' => InfraestructurasPisosDiseno::route('/{record}/diseno'),
            'tiendas-propietarios' => TiendasPropietarios::route('/{record}/tiendas-propietarios'),
        ];
    }
}
