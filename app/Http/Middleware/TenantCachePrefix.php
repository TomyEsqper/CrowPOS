<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantCachePrefix
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply cache prefix if we're in a tenant context
        if (tenancy()->initialized) {
            $tenantId = tenant('id');
            
            if ($tenantId) {
                // Set cache prefix to include tenant ID
                config([
                    'cache.prefix' => 'tenant_' . $tenantId . '_cache',
                ]);
            }
        }

        return $next($request);
    }
}
