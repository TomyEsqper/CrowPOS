<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantSessionCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar si estamos en contexto de tenant
        if (tenancy()->initialized) {
            $tenantId = tenant('id');
            
            if ($tenantId) {
                // Configurar cookie de sesión única por tenant
                config([
                    'session.cookie' => 'tenant_' . $tenantId . '_session',
                    'session.path' => '/',
                    'session.domain' => null,
                    'session.secure' => $request->secure(),
                    'session.http_only' => true,
                    'session.same_site' => 'lax',
                ]);
            }
        }

        return $next($request);
    }
}
