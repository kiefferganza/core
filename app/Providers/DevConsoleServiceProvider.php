<?php

namespace App\Providers;

use App\Http\Middleware\DevConsoleAuth;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class DevConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register middleware for dev console
        $this->app['router']->aliasMiddleware('DevConsoleAuth', DevConsoleAuth::class);

        $this->registerTelescope();
        $this->registerHorizon();
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            // Prune telescope records that 7 days old
            $schedule->command('telescope:prune --hours=168')->daily();
            // Record horizon metrics
            $schedule->command('horizon:snapshot')->everyFiveMinutes();
        });
    }

    private function registerTelescope(): void
    {
        if (config('dev.telescope_enabled')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    private function registerHorizon(): void
    {
        if (config('dev.horizon_enabled')) {
            $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
            $this->app->register(HorizonServiceProvider::class);
        }
    }
}
