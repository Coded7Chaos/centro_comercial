<?php

namespace Tests\Feature;

use App\Filament\Widgets\PagosMonthNavigator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class PagosMonthNavigatorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pagos_month_navigator_is_available_by_filament_livewire_alias(): void
    {
        session([
            'pagos_nav_mes' => 6,
            'pagos_nav_anio' => 2026,
        ]);

        Livewire::test('app.filament.widgets.pagos-month-navigator')
            ->assertSet('mes', 6)
            ->assertSet('anio', 2026);
    }

    public function test_pagos_month_navigator_changes_month_and_syncs_session(): void
    {
        session([
            'pagos_nav_mes' => 1,
            'pagos_nav_anio' => 2026,
        ]);

        Livewire::test(PagosMonthNavigator::class)
            ->call('previousMonth')
            ->assertSet('mes', 12)
            ->assertSet('anio', 2025)
            ->assertDispatched('pagosNavChanged', mes: 12, anio: 2025)
            ->call('nextMonth')
            ->assertSet('mes', 1)
            ->assertSet('anio', 2026)
            ->assertDispatched('pagosNavChanged', mes: 1, anio: 2026);

        $this->assertSame(1, session('pagos_nav_mes'));
        $this->assertSame(2026, session('pagos_nav_anio'));
    }
}
