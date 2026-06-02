<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
