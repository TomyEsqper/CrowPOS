<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts())) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        // Clear rate limit on successful login
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 302) {
            RateLimiter::clear($key);
        }

        return $response;
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $tenantId = tenancy()->initialized ? tenant('id') : 'landlord';
        $email = $request->input('email', 'unknown');
        $ip = $request->ip();
        
        return "login:{$tenantId}:{$email}:{$ip}";
    }

    /**
     * Maximum number of attempts
     */
    protected function maxAttempts(): int
    {
        return (int) env('RATE_LIMIT_LOGIN_ATTEMPTS', 5);
    }

    /**
     * Decay minutes for rate limiting
     */
    protected function decayMinutes(): int
    {
        return (int) env('RATE_LIMIT_LOGIN_DECAY_MINUTES', 1);
    }
}
