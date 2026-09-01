<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('queue:work --stop-when-empty --tries=3 --max-time=45')
            ->everyMinute()
            ->withoutOverlapping(2);
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin'    => \App\Http\Middleware\SuperAdminMiddleware::class,
            'company_admin' => \App\Http\Middleware\CompanyAdminMiddleware::class,
            'employee'      => \App\Http\Middleware\EmployeeMiddleware::class,
            'client'        => \App\Http\Middleware\ClientMiddleware::class,
            'company_slug'  => \App\Http\Middleware\CompanySlugMiddleware::class,
        ]);

        // Behind a reverse proxy / CDN (e.g. Cloudflare, Nginx SSL termination) in production,
        // Laravel otherwise can't tell the original request was HTTPS — it then can't reliably
        // decide whether to mark the session cookie Secure, which causes the cookie (and the
        // CSRF token stored in the session) to get dropped/mismatched, surfacing as 419 errors.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
