<?php

namespace App\Providers;

use App\Helpers\Configuration;
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
        $this->app->singleton(SpinupWp::class, fn ($app) => new SpinupWp());

        $this->app->singleton(Configuration::class, fn ($app) => new Configuration());
    }
}
