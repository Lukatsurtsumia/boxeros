<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // In production, always generate https:// links so assets/forms work behind SSL.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
