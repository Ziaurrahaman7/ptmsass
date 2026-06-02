<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompanySlugMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $slug    = $request->route('slug');
        $company = auth()->user()?->company;

        if (!$company || $company->slug !== $slug) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
