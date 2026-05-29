<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\Infraestructuras;

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
        Schema::defaultStringLength(100);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        View::composer('components.public-navbar', function ($view) {
            $infraestructura = Infraestructuras::first();
            $view->with('mallName', $infraestructura ? $infraestructura->nombre : 'Infraestructuras');
        });

        \App\Models\Suscripciones::observe(\App\Observers\SuscripcionObserver::class);
    }
}
