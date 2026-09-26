<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = (string) $request->bearerToken();
        abort_if($bearer === '', 401);

        $token = ApiToken::query()->where('token', hash('sha256', $bearer))->first();
        abort_if(! $token, 401);

        $token->update(['last_used_at' => now()]);
        $request->setUserResolver(fn () => $token->user);
        auth()->setUser($token->user);

        return $next($request);
    }
}
