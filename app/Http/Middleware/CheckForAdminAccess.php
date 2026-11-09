<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckForAdminAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        // @todo: expand to a role/permission check (e.g. $user->hasRole('admin')) once a permission system is in place — currently any authenticated user passes
        if (! $request->user()) {
            abort(403);
        }

        return $next($request);
    }
}
