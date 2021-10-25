<?php

namespace App\Providers;

use DeliciousBrains\SpinupWp\SpinupWp;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SpinupWp::class, fn ($app, $params) => new SpinupWp($params['apiKey'], $params['client']));
    }
}
