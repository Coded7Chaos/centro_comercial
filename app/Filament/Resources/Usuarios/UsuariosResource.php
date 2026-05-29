<?php

namespace App\Filament\Resources\Usuarios;

use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\Resources\Usuarios\Pages\ViewUsuarios;
use App\Filament\Resources\Usuarios\Schemas\UsuariosForm;
use App\Filament\Resources\Usuarios\Schemas\UsuariosInfolist;
use App\Filament\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Usuarios\Pages\CreateUsuariosCustom;
use App\Filament\Resources\Usuarios\Pages\EditUsuariosCustom;

class UsuariosResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?string $navigationLabel = 'Administradores';

    protected static ?string $modelLabel = 'Administrador';

    protected static ?string $pluralModelLabel = 'Administradores';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'nombres';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            });
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if ($user && $user->hasRole('admin')) {
            return false;
        }

        return parent::canCreate();
    }

    public static function form(Schema $schema): Schema
    {
        return UsuariosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UsuariosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::configure($table);
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
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuariosCustom::route('/create'),
            'view' => ViewUsuarios::route('/{record}'),
            'edit' => EditUsuariosCustom::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
