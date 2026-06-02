<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompanyAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->isCompanyAdmin()) {
            abort(403, 'Access denied.');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $company = $user->company;

        if (!$company || $company->status === 'suspended') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your company account has been suspended. Please contact support.']);
        }

        if ($company->status === 'inactive') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your company account is inactive.']);
        }

        return $next($request);
    }
}
