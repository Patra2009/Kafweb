<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('auth', fn (Request $request): Limit => Limit::perMinute(5)->by(
            $request->ip().'|'.(string) $request->input('username'),
        ));

        // Rate limit for public checkout: 10 orders per minute per IP
        RateLimiter::for('checkout', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        // Rate limit for tracking status: 30 requests per minute per IP
        RateLimiter::for('tracking', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip()));
    }
}
