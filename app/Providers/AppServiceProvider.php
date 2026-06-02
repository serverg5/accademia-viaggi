<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }

    // 🔥 FIX TEMP CACHE PROBLEMI RENDER
    if (app()->environment('production')) {
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
    }
}
}
