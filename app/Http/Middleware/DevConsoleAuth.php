<?php

namespace App\Http\Middleware;

use Closure;

class DevConsoleAuth
{
    public function handle($request, Closure $next): mixed
    {
        if (! config('dev.enable_devconsole')) {
            abort(404);
        }

        if (empty(config('dev.dev_console_username')) || empty(config('dev.dev_console_password'))) {
            abort(500, 'DEV_CONSOLE_USERNAME and DEV_CONSOLE_PASSWORD must be set.');
        }

        $AUTH_USER = config('dev.dev_console_username');
        $AUTH_PASS = config('dev.dev_console_password');

        header('Cache-Control: no-cache, must-revalidate, max-age=0');

        // Check if the credential is provided
        $hasCredentials = ! (empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));

        // Compare the credentials
        $isNotAuthenticated = (! $hasCredentials ||
            $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
            $_SERVER['PHP_AUTH_PW'] != $AUTH_PASS);

        if ($isNotAuthenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }

        return $next($request);
    }
}
