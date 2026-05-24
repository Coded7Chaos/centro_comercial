<?php

namespace App\Filament\Resources\Suscripciones;

use App\Filament\Resources\Suscripciones\Pages\CreateSuscripciones;
use App\Filament\Resources\Suscripciones\Pages\EditSuscripciones;
use App\Filament\Resources\Suscripciones\Pages\ListSuscripciones;
use App\Filament\Resources\Suscripciones\Schemas\SuscripcionesForm;
use App\Filament\Resources\Suscripciones\Tables\SuscripcionesTable;
use App\Models\Suscripciones;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SuscripcionesResource extends Resource
{
    protected static ?string $model = Suscripciones::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Suscripciones';

    protected static ?string $navigationLabel = 'Gestión de Contratos';

    protected static ?string $modelLabel = 'Suscripción';

    protected static ?string $pluralModelLabel = 'Gestión de Contratos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SuscripcionesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuscripcionesTable::configure($table);
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
            'index' => ListSuscripciones::route('/'),
            'create' => CreateSuscripciones::route('/create'),
            'edit' => EditSuscripciones::route('/{record}/edit'),
        ];
    }
}
