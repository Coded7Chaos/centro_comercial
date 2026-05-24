<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\InfraestructurasTiendas;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Grid;
use Illuminate\Database\Eloquent\Builder;

class DirectorioInterno extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected string $view = 'filament.pages.directorio-interno';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:DirectorioInterno') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InfraestructurasTiendas::query()
                    ->whereHas('marcas')
                    ->with(['marcas', 'piso'])
            )
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('marcas.nombre')
                        ->label('Marca')
                        ->weight('bold')
                        ->size('lg')
                        ->badge()
                        ->separator(', ')
                        ->searchable(),
                    TextColumn::make('numero')
                        ->prefix('Local: ')
                        ->color('gray'),
                    TextColumn::make('piso.nombre')
                        ->prefix('Piso: ')
                        ->color('primary'),
                ])->space(2),
            ])
            ->paginated([9, 18, 36]);
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Directorio Interno (Grid)';
    }
}
