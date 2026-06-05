<?php

namespace App\Providers;

use App\Filament\Widgets\CobrosMonthNavigator;
use App\Filament\Widgets\PagosMonthNavigator;
use App\Models\Infraestructuras;
use App\Models\Suscripciones;
use App\Models\User;
use App\Observers\SuscripcionObserver;
use App\Observers\UserObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureSystemDate();

        Livewire::component('app.filament.widgets.pagos-month-navigator', PagosMonthNavigator::class);
        Livewire::component('app.filament.widgets.cobros-month-navigator', CobrosMonthNavigator::class);

        Schema::defaultStringLength(100);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        View::composer('components.public-navbar', function ($view) {
            $infraestructuras = Infraestructuras::select('id', 'nombre')->get();

            $activeId = request('infraestructura_id');
            $activeInfra = null;
            if ($activeId) {
                $activeInfra = $infraestructuras->firstWhere('id', $activeId);
            }
            if (! $activeInfra) {
                $activeInfra = $infraestructuras->first();
            }

            $view->with([
                'mallName' => $activeInfra ? $activeInfra->nombre : 'Infraestructuras',
                'todasInfraestructuras' => $infraestructuras,
                'activeInfraestructuraId' => $activeInfra ? $activeInfra->id : null,
            ]);
        });

        Suscripciones::observe(SuscripcionObserver::class);
        User::observe(UserObserver::class);
    }

    private function configureSystemDate(): void
    {
        $configuredDate = trim((string) config('app.fecha_sistema', ''));

        if ($configuredDate === '' || strcasecmp($configuredDate, 'null') === 0) {
            Date::setTestNow();
            Carbon::setTestNow();

            return;
        }

        $timezone = config('app.timezone', 'UTC');
        try {
            $date = CarbonImmutable::createFromFormat('!d/m/Y', $configuredDate, $timezone);
        } catch (\Throwable) {
            $date = null;
        }

        if (! $date || $date->format('d/m/Y') !== $configuredDate) {
            throw new InvalidArgumentException('FECHA_SISTEMA debe estar vacía o tener formato DD/MM/YYYY.');
        }

        $systemDateResolver = function () use ($date, $timezone): \DateTimeImmutable {
            $realCurrentTime = new \DateTimeImmutable('now', new \DateTimeZone($timezone));

            return \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $date->format('Y-m-d').' '.$realCurrentTime->format('H:i:s'),
                new \DateTimeZone($timezone),
            );
        };

        Date::setTestNow($systemDateResolver);
        Carbon::setTestNow($systemDateResolver);
    }
}
