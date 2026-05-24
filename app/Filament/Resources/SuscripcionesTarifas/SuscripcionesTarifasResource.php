<?php

namespace App\Filament\Resources\SuscripcionesTarifas;

use App\Filament\Resources\SuscripcionesTarifas\Pages\CreateSuscripcionesTarifas;
use App\Filament\Resources\SuscripcionesTarifas\Pages\EditSuscripcionesTarifas;
use App\Filament\Resources\SuscripcionesTarifas\Pages\ListSuscripcionesTarifas;
use App\Filament\Resources\SuscripcionesTarifas\Schemas\SuscripcionesTarifasForm;
use App\Filament\Resources\SuscripcionesTarifas\Tables\SuscripcionesTarifasTable;
use App\Models\SuscripcionesTarifas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SuscripcionesTarifasResource extends Resource
{
    protected static ?string $model = SuscripcionesTarifas::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';

    protected static ?string $navigationLabel = 'Tarifas';

    protected static ?string $modelLabel = 'Tarifa';

    protected static ?string $pluralModelLabel = 'Tarifas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function form(Schema $schema): Schema
    {
        return SuscripcionesTarifasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuscripcionesTarifasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSuscripcionesTarifas::route('/'),
            'create' => CreateSuscripcionesTarifas::route('/create'),
            'edit'   => EditSuscripcionesTarifas::route('/{record}/edit'),
        ];
    }
}
