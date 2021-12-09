<?php

namespace App\Providers;

use App\Helpers\Configuration;
use App\Repositories\SpinupWpRepository;
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
        $this->app->singleton(SpinupWpRepository::class, function() {
            return new SpinupWpRepository(new SpinupWp());
        });

        $this->app->singleton(Configuration::class, function () {
            $path = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing'
                ? base_path('tests')
                : ($_SERVER['HOME'] ?? $_SERVER['USERPROFILE']);

            $path .= '/.spinupwp/';

            return new Configuration($path);
        });
    }
}
