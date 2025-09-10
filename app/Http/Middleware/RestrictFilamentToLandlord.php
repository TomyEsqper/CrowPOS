<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictFilamentToLandlord
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we're in a tenant context
        if (tenancy()->initialized) {
            // Block access to Filament admin panel from tenant subdomains
            abort(403, 'Access denied. Filament admin panel is only available on the main domain.');
        }

        // Check if we're on a tenant subdomain
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);
        
        if (!in_array($host, $centralDomains) && !$this->isLocalhost($host)) {
            abort(403, 'Access denied. Filament admin panel is only available on the main domain.');
        }

        return $next($request);
    }

    /**
     * Check if the host is localhost or local development
     */
    private function isLocalhost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1']) || 
               str_ends_with($host, '.localhost') ||
               str_ends_with($host, '.local');
    }
}
