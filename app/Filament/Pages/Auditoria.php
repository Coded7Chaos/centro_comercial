<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Auditoria extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Auditoría del Sistema';
    protected static ?string $title = 'Auditoría del Sistema';
    protected static string|\UnitEnum|null $navigationGroup = 'Administración';
    protected string $view = 'filament.pages.auditoria';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:Auditoria') ?? false;
    }

    protected static function categorias(): array
    {
        return [
            'usuarios'        => 'Usuarios',
            'clientes'        => 'Clientes',
            'productos'       => 'Productos',
            'categorias'      => 'Categorías',
            'marcas'          => 'Marcas',
            'suscripciones'   => 'Suscripciones / Cobros / Pagos',
            'tarifas'         => 'Tarifas',
            'infraestructura' => 'Infraestructura',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()->with(['causer', 'subject'])
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('log_name')
                    ->label('Categoría')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::categorias()[$state] ?? ucfirst((string) $state))
                    ->color(fn ($state) => match ($state) {
                        'usuarios'        => 'info',
                        'clientes'        => 'info',
                        'suscripciones'   => 'success',
                        'tarifas'         => 'warning',
                        'productos'       => 'primary',
                        'categorias'      => 'primary',
                        'marcas'          => 'primary',
                        'infraestructura' => 'gray',
                        default           => 'gray',
                    }),
                TextColumn::make('description')
                    ->label('Acción')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'created' => 'Creó',
                        'updated' => 'Actualizó',
                        'deleted' => 'Eliminó',
                        default   => ucfirst((string) $state),
                    }),
                TextColumn::make('subject_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
                TextColumn::make('subject_id')
                    ->label('ID')
                    ->placeholder('—'),
                TextColumn::make('causer.nombres')
                    ->label('Hecho por')
                    ->placeholder('Sistema'),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Categoría')
                    ->options(static::categorias()),
                SelectFilter::make('description')
                    ->label('Acción')
                    ->options([
                        'created' => 'Creó',
                        'updated' => 'Actualizó',
                        'deleted' => 'Eliminó',
                    ]),
                SelectFilter::make('causer_id')
                    ->label('Usuario')
                    ->options(fn () => User::query()->orderBy('nombres')->pluck('nombres', 'id')->all())
                    ->searchable(),
                Filter::make('rango_fechas')
                    ->schema([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $q, array $data): Builder {
                        return $q
                            ->when($data['desde'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['hasta'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->recordActions([
                Action::make('detalle')
                    ->label('Ver cambios')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalle del registro de auditoría')
                    ->modalContent(fn (Activity $record) => view('filament.components.auditoria-detalle', [
                        'log' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ]);
    }
}
