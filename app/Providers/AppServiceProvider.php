<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        // Set application locale based on authenticated user's language preference
        // This will apply to all views and translations
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->language) {
                app()->setLocale($user->language);
            }
        } elseif (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
    }
}
