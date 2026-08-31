<?php

namespace App\Providers;

use App\Models\Priority;
use App\Services\PlatformMail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PlatformMail::apply();

        // Share $slug and the company's customizable priority list globally with all views
        // (memoized per-request so partials/@includes don't each re-query it).
        view()->composer('*', function ($view) {
            static $priorities = null;

            if (auth()->check() && auth()->user()->company) {
                $view->with('slug', auth()->user()->company->slug);

                if ($priorities === null) {
                    $priorities = Priority::forCompany(auth()->user()->company_id);
                }
                $view->with('companyPriorities', $priorities);
            }
        });
    }
}
