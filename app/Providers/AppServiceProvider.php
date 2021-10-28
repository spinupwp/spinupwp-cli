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
        $this->app->singleton(SpinupWp::class, function ($app) {
            return new SpinupWp($this->apiToken('default'));
        });
    }

    protected function apiToken($profile): string
    {
        return (new Configuration)->get('api_token', $profile) ?? '';
    }
}
