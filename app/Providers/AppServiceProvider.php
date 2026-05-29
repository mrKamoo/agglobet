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
        if (!app()->runningInConsole()) {
            view()->composer('*', function ($view) {
                // Resolve once per request using a static variable
                static $globalActiveSeason = null;
                static $resolved = false;

                if (!$resolved) {
                    try {
                        $globalActiveSeason = \App\Models\Season::where('is_active', true)->first();
                    } catch (\Illuminate\Database\QueryException $e) {
                        $globalActiveSeason = null;
                    }
                    $resolved = true;
                }

                $view->with('globalActiveSeason', $globalActiveSeason);
            });
        }
    }
}
