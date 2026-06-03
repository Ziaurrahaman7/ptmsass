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

        // Debug logging
        \Log::info('CompanySlug Middleware Debug', [
            'url_slug' => $slug,
            'user_id' => auth()->id(),
            'user_company_id' => auth()->user()?->company_id,
            'company_slug' => $company?->slug ?? 'NULL',
            'user_role' => auth()->user()?->role,
        ]);

        if (!$company || $company->slug !== $slug) {
            abort(403, 'Slug mismatch: URL=' . $slug . ', Company=' . ($company?->slug ?? 'NULL'));
        }

        return $next($request);
    }
}
