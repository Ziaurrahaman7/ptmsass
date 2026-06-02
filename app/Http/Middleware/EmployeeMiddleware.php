<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->isEmployee()) {
            abort(403, 'Access denied.');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $company = $user->company;

        if (!$company || $company->status === 'suspended') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your company account has been suspended.']);
        }

        return $next($request);
    }
}
