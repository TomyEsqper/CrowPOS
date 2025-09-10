<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Health check routes (public, no auth required)
            Route::group([], base_path('routes/health.php'));
            
            // Tenant routes with isolated session cookies and cache prefix
            Route::middleware([
                'web', 
                'tenant', 
                \App\Http\Middleware\TenantSessionCookie::class,
                \App\Http\Middleware\TenantCachePrefix::class
            ])
                ->group(base_path('routes/tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global request ID middleware (before logging)
        $middleware->prepend(\App\Http\Middleware\RequestId::class);
        
        // Global CSP middleware for all requests
        $middleware->append(\App\Http\Middleware\ContentSecurityPolicy::class);
        
        // Global security headers (except in local environment)
        if (!app()->environment('local')) {
            $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
