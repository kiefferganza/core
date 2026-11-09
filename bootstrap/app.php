<?php

use App\Http\Middleware\ApiResponseMiddleware;
use App\Http\Middleware\ConvertStringToBoolean;
use App\Http\Middleware\PreventIndexingAppLevel;
use Appetiser\Auth\Middleware\SetUserTimezoneContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api',
        then: function () {
            Route::middleware('api')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ApiResponseMiddleware::class,
            SetUserTimezoneContext::class,
        ]);

        // Global middleware stack
        $middleware->use([
            HandleCors::class,
            ValidatePostSize::class,
            ConvertStringToBoolean::class,
            ConvertEmptyStringsToNull::class,
            PreventIndexingAppLevel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
