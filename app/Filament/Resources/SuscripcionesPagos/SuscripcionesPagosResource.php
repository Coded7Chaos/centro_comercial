<?php

namespace App\Filament\Resources\SuscripcionesPagos;

use App\Filament\Resources\SuscripcionesPagos\Pages\CreateSuscripcionesPagos;
use App\Filament\Resources\SuscripcionesPagos\Pages\EditSuscripcionesPagos;
use App\Filament\Resources\SuscripcionesPagos\Pages\ListSuscripcionesPagos;
use App\Filament\Resources\SuscripcionesPagos\Pages\ViewSuscripcionesPagos;
use App\Filament\Resources\SuscripcionesPagos\Schemas\SuscripcionesPagosForm;
use App\Filament\Resources\SuscripcionesPagos\Schemas\SuscripcionesPagosInfolist;
use App\Filament\Resources\SuscripcionesPagos\Tables\SuscripcionesPagosTable;
use App\Models\SuscripcionesPagos;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SuscripcionesPagosResource extends Resource
{
    protected static ?string $model = SuscripcionesPagos::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';

    protected static ?string $navigationLabel = 'Pagos';

    protected static ?string $modelLabel = 'Pago';

    protected static ?string $pluralModelLabel = 'Pagos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SuscripcionesPagosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SuscripcionesPagosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuscripcionesPagosTable::configure($table);
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
            'index' => ListSuscripcionesPagos::route('/'),
            'create' => CreateSuscripcionesPagos::route('/create'),
            'view' => ViewSuscripcionesPagos::route('/{record}'),
            'edit' => EditSuscripcionesPagos::route('/{record}/edit'),
        ];
    }
}
