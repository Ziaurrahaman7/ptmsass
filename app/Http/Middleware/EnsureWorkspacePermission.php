<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureWorkspacePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, 'You do not have permission for this action.');
        }

        return $next($request);
    }
}
