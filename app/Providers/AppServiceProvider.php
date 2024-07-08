<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // config expires_at for personal access token in sanctum
        config(['sanctum.expiration' => 60]);

        // config unauthorized response for api
        config(['api.defaults.unauthorized_response' => 'Unauthorized']);


    }
}
