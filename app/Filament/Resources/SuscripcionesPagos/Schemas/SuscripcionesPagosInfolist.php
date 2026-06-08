<?php

namespace App\Filament\Resources\SuscripcionesPagos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SuscripcionesPagosInfolist
{
    private static function formatLocation($record): string
    {
        $piso = $record?->cobro?->suscripcion?->infraestructurasTienda?->piso;
        $infra = $piso?->infraestructura;
        $pisoNombre = $piso?->nombre ?? '---';
        $pisoNumero = $piso?->numero ? " ({$piso->numero})" : '';

        return ($infra?->nombre ?? 'Sin infraestructura')
            . ' - Piso '
            . $pisoNombre
            . $pisoNumero;
    }

    private static function formatPaymentDifference($record): string
    {
        $fechaPago = $record?->fecha_pago;
        $fechaVencimiento = $record?->cobro?->fecha_vencimiento;

        if (! $fechaPago || ! $fechaVencimiento) {
            return '---';
        }

        $difference = (int) \Carbon\Carbon::parse($fechaVencimiento)
            ->startOfDay()
            ->diffInDays(\Carbon\Carbon::parse($fechaPago)->startOfDay(), false);

        if ($difference > 0) {
            return "+{$difference} días (retrasado)";
        }

        if ($difference < 0) {
            return "{$difference} días (adelantado)";
        }

        return '0 días';
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('cliente')
                    ->label('Cliente')
                    ->state(function ($record): string {
                        $user = $record?->cobro?->suscripcion?->cliente?->user;

                        return $user
                            ? trim("{$user->nombres} {$user->apellido_paterno} {$user->apellido_materno}")
                            : '---';
                    }),

                TextEntry::make('tienda')
                    ->label('Tienda')
                    ->state(fn ($record): string => $record?->cobro?->suscripcion?->infraestructurasTienda?->nombre ?? '---'),

                TextEntry::make('concepto')
                    ->label('Concepto de cobro')
                    ->state(fn ($record): string => $record?->cobro?->concepto ?? '---')
                    ->columnSpanFull(),

                TextEntry::make('localizacion')
                    ->label('Localización')
                    ->state(fn ($record): string => self::formatLocation($record)),

                TextEntry::make('total_pagado')
                    ->label('Monto abonado')
                    ->state(fn ($record) => $record?->cobro?->pagos()->sum('monto_pagado') ?? 0)
                    ->money('BOB'),

                TextEntry::make('monto_pagado')
                    ->label('Monto pagado')
                    ->money('BOB'),

                TextEntry::make('fecha_pago')
                    ->label('Fecha de pago')
                    ->date('d/m/Y'),

                TextEntry::make('dias_diferencia')
                    ->label('Días de diferencia')
                    ->state(fn ($record): string => self::formatPaymentDifference($record)),

                TextEntry::make('metodo_pago')
                    ->label('Método de pago')
                    ->formatStateUsing(fn ($state): string => ucfirst((string) $state)),

                TextEntry::make('nombre_pagador')
                    ->label('Nombre de quien realizó el pago')
                    ->placeholder('---')
                    ->visible(fn ($record): bool => $record?->metodo_pago === 'efectivo'),

                TextEntry::make('referencia')
                    ->label('Número de Transacción / Clave de rastreo')
                    ->state(fn ($record): string => $record?->referencia ?? $record?->numero_transaccion ?? '---')
                    ->visible(fn ($record): bool => $record?->metodo_pago === 'transferencia'),

                TextEntry::make('banco_origen')
                    ->label('Banco de origen')
                    ->placeholder('---')
                    ->visible(fn ($record): bool => $record?->metodo_pago === 'transferencia'),

                TextEntry::make('nombre_titular')
                    ->label('Nombre del titular')
                    ->state(fn ($record): string => $record?->nombre_titular ?? $record?->titular_transferencia ?? '---')
                    ->visible(fn ($record): bool => $record?->metodo_pago === 'transferencia'),

                TextEntry::make('hora_pago')
                    ->label('Hora de pago')
                    ->placeholder('---'),

                TextEntry::make('observaciones')
                    ->label('Observaciones')
                    ->placeholder('Sin observaciones')
                    ->columnSpanFull(),

                \Filament\Infolists\Components\ImageEntry::make('comprobante')
                    ->label('Vista previa del comprobante')
                    ->visible(fn ($record) => $record?->comprobante && in_array(strtolower(pathinfo($record->comprobante, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])),

                TextEntry::make('comprobante_link')
                    ->label('Comprobante de Pago')
                    ->state(fn ($record) => $record?->comprobante ? 'Abrir comprobante en nueva pestaña ↗' : '---')
                    ->url(fn ($record) => $record?->comprobante ? \Illuminate\Support\Facades\Storage::url($record->comprobante) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record?->comprobante)),
            ]);
    }
}
