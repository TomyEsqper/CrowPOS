<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        // Configure rate limiting for login attempts
        RateLimiter::for('login', function (Request $request) {
            $tenantId = tenancy()->initialized ? tenant('id') : 'landlord';
            $email = $request->input('email', 'unknown');
            $ip = $request->ip();
            
            $key = "login:{$tenantId}:{$email}:{$ip}";
            
            return Limit::perMinute(5, $key)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Configure rate limiting for API endpoints
        RateLimiter::for('api', function (Request $request) {
            $tenantId = tenancy()->initialized ? tenant('id') : 'landlord';
            $userId = $request->user()?->id ?? 'guest';
            $ip = $request->ip();
            
            $key = "api:{$tenantId}:{$userId}:{$ip}";
            
            return Limit::perMinute(60, $key);
        });

        // Configure rate limiting for sensitive operations
        RateLimiter::for('sensitive', function (Request $request) {
            $tenantId = tenancy()->initialized ? tenant('id') : 'landlord';
            $userId = $request->user()?->id ?? 'guest';
            $ip = $request->ip();
            
            $key = "sensitive:{$tenantId}:{$userId}:{$ip}";
            
            return Limit::perMinute(10, $key);
        });

        // Configure rate limiting for health check endpoint
        RateLimiter::for('health', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}