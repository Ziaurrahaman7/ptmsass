<?php

namespace App\Providers;

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
        // Share $slug globally with all views
        view()->composer('*', function ($view) {
            if (auth()->check() && auth()->user()->company) {
                $view->with('slug', auth()->user()->company->slug);
            }
        });
    }
}
