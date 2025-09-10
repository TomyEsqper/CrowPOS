<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique nonce for this request
        $nonce = bin2hex(random_bytes(16));
        
        // Store nonce in request attributes for use in views
        $request->attributes->set('csp_nonce', $nonce);
        
        $response = $next($request);
        
        // Build CSP header with nonce
        $csp = $this->buildCSP($nonce);
        $response->headers->set('Content-Security-Policy', $csp);
        
        return $response;
    }

    /**
     * Build Content Security Policy header with nonce
     */
    private function buildCSP(string $nonce): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'", // Removed unsafe-eval for better security
            "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' https: data: blob:",
            "connect-src 'self' ws: wss:", // WebSocket support for Livewire
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "media-src 'self'",
            "worker-src 'self' blob:",
        ];

        return implode('; ', $directives);
    }
}
