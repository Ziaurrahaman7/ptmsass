<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompanyAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        \Log::info('CompanyAdmin Middleware Debug', [
            'user_id' => $user?->id,
            'is_company_admin' => $user?->isCompanyAdmin(),
            'is_active' => $user?->is_active,
            'company_status' => $user?->company?->status,
            'url' => $request->url(),
        ]);

        if (!$user || !$user->isCompanyAdmin()) {
            abort(403, 'Not company admin: role=' . ($user?->role ?? 'null'));
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
