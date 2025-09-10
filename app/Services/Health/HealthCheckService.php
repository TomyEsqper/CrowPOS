<?php

namespace App\Services\Health;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class HealthCheckService
{
    /**
     * Perform all health checks
     */
    public function performChecks(): array
    {
        $checks = [
            'db_landlord' => $this->checkDatabaseLandlord(),
            'db_tenant' => $this->checkDatabaseTenant(),
            'redis' => $this->checkRedis(),
            'horizon' => $this->checkHorizon(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn($status) => $status === 'healthy');

        return [
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'version' => $this->getVersionInfo(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Check landlord database connection
     */
    private function checkDatabaseLandlord(): string
    {
        try {
            DB::connection('landlord')->select('SELECT 1');
            return 'healthy';
        } catch (Exception $e) {
            Log::warning('Health check: Landlord database connection failed', [
                'error' => $e->getMessage()
            ]);
            return 'unhealthy';
        }
    }

    /**
     * Check tenant database connection (if tenant is active)
     */
    private function checkDatabaseTenant(): string
    {
        try {
            if (!tenancy()->initialized) {
                return 'skipped';
            }

            DB::select('SELECT 1');
            return 'healthy';
        } catch (Exception $e) {
            Log::warning('Health check: Tenant database connection failed', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage()
            ]);
            return 'unhealthy';
        }
    }

    /**
     * Check Redis connection
     */
    private function checkRedis(): string
    {
        try {
            // Check if Redis is configured
            if (config('cache.default') !== 'redis' && config('session.driver') !== 'redis') {
                return 'skipped';
            }
            
            // Test Redis connection with a simple ping
            Redis::connection()->ping();
            
            // Test cache functionality
            $testKey = 'healthz_test_' . uniqid();
            Cache::store('redis')->put($testKey, 'test', 10);
            $value = Cache::store('redis')->get($testKey);
            Cache::store('redis')->forget($testKey);
            
            if ($value !== 'test') {
                return 'unhealthy';
            }
            
            return 'healthy';
        } catch (Exception $e) {
            Log::warning('Health check: Redis connection failed', [
                'error' => $e->getMessage()
            ]);
            return 'unhealthy';
        }
    }

    /**
     * Check Horizon status (if available)
     */
    private function checkHorizon(): string
    {
        try {
            if (!class_exists(\Laravel\Horizon\Horizon::class)) {
                return 'skipped';
            }

            $status = \Laravel\Horizon\Horizon::status();
            
            // Check if Horizon is running and has active workers
            if ($status === 'active' || $status === 'paused') {
                return 'healthy';
            }
            
            return 'unhealthy';
        } catch (Exception $e) {
            Log::warning('Health check: Horizon check failed', [
                'error' => $e->getMessage()
            ]);
            return 'unhealthy';
        }
    }

    /**
     * Check storage write permissions
     */
    private function checkStorage(): string
    {
        try {
            $testFile = 'healthz_test_' . uniqid() . '.tmp';
            $testContent = now()->toISOString();
            
            // Test write
            Storage::disk('local')->put($testFile, $testContent);
            
            // Test read
            $content = Storage::disk('local')->get($testFile);
            
            // Test delete
            Storage::disk('local')->delete($testFile);
            
            if ($content !== $testContent) {
                return 'unhealthy';
            }
            
            return 'healthy';
        } catch (Exception $e) {
            Log::warning('Health check: Storage check failed', [
                'error' => $e->getMessage()
            ]);
            return 'unhealthy';
        }
    }

    /**
     * Get version information
     */
    private function getVersionInfo(): array
    {
        return [
            'app' => env('APP_VERSION', '0.1.0'),
            'git_sha' => env('GIT_SHA', 'unknown'),
        ];
    }
}
