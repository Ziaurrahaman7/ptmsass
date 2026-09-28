<?php

namespace App\Http\Middleware;

use App\Support\AdminRoutePermissions;
use Closure;
use Illuminate\Http\Request;

class EnsureAdminPortalPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (! $user || ! $user->isCompanyAdmin()) {
            abort(403);
        }

        $permission = AdminRoutePermissions::forRoute($request->route()?->getName());
        if ($permission && ! $user->hasPermission($permission)) {
            abort(403, 'You do not have permission for this area.');
        }

        return $next($request);
    }
}
