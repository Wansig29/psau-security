<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        if (env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Auto-reconnect on Railway proxy-induced "MySQL server has gone away" drops.
        // Laravel will transparently retry the failed query after reconnecting.
        DB::whenDisconnected(function () {
            Log::warning('DB connection lost (Railway proxy drop). Reconnecting...');
        });
    }
}
