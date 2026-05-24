<?php

namespace App\Filament\Resources\SuscripcionesCobros;

use App\Filament\Resources\SuscripcionesCobros\Pages\CreateSuscripcionesCobros;
use App\Filament\Resources\SuscripcionesCobros\Pages\EditSuscripcionesCobros;
use App\Filament\Resources\SuscripcionesCobros\Pages\ListSuscripcionesCobros;
use App\Filament\Resources\SuscripcionesCobros\Pages\ViewSuscripcionesCobros;
use App\Filament\Resources\SuscripcionesCobros\Schemas\SuscripcionesCobrosForm;
use App\Filament\Resources\SuscripcionesCobros\Schemas\SuscripcionesCobrosInfolist;
use App\Filament\Resources\SuscripcionesCobros\Tables\SuscripcionesCobrosTable;
use App\Models\SuscripcionesCobros;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SuscripcionesCobrosResource extends Resource
{
    protected static ?string $model = SuscripcionesCobros::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';

    protected static ?string $navigationLabel = 'Cobros';

    protected static ?string $modelLabel = 'Cobro';

    protected static ?string $pluralModelLabel = 'Cobros';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'id';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return SuscripcionesCobrosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SuscripcionesCobrosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuscripcionesCobrosTable::configure($table);
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
            'index' => ListSuscripcionesCobros::route('/'),
            'create' => CreateSuscripcionesCobros::route('/create'),
            'view' => ViewSuscripcionesCobros::route('/{record}'),
            'edit' => EditSuscripcionesCobros::route('/{record}/edit'),
        ];
    }
}
