<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mockery;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test healthy status when all services are working
     */
    public function test_health_check_returns_healthy_status(): void
    {
        // Mock the health check service to return healthy status
        $this->mock(\App\Services\Health\HealthCheckService::class, function ($mock) {
            $mock->shouldReceive('performChecks')
                ->andReturn([
                    'status' => 'healthy',
                    'checks' => [
                        'db_landlord' => 'healthy',
                        'db_tenant' => 'skipped',
                        'redis' => 'healthy',
                        'horizon' => 'skipped',
                        'storage' => 'healthy'
                    ],
                    'version' => [
                        'app' => '0.1.0',
                        'git_sha' => 'unknown'
                    ],
                    'timestamp' => now()->toISOString()
                ]);
        });

        $response = $this->get('/healthz');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'checks' => [
                'db_landlord',
                'db_tenant',
                'redis',
                'horizon',
                'storage'
            ],
            'version' => [
                'app',
                'git_sha'
            ],
            'timestamp'
        ]);

        $data = $response->json();
        $this->assertEquals('healthy', $data['status']);
        $this->assertEquals('healthy', $data['checks']['db_landlord']);
        $this->assertEquals('skipped', $data['checks']['db_tenant']);
        $this->assertEquals('healthy', $data['checks']['redis']);
        $this->assertEquals('skipped', $data['checks']['horizon']);
        $this->assertEquals('healthy', $data['checks']['storage']);
        
        // Check version information
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('app', $data['version']);
        $this->assertArrayHasKey('git_sha', $data['version']);
        
        // Check X-Request-Id header
        $this->assertTrue($response->headers->has('X-Request-Id'), 'Response should include X-Request-Id header');
        $requestId = $response->headers->get('X-Request-Id');
        $this->assertNotEmpty($requestId, 'X-Request-Id should not be empty');
    }

    /**
     * Test unhealthy status when Redis fails
     */
    public function test_health_check_returns_unhealthy_when_redis_fails(): void
    {
        // Mock the health check service to return unhealthy status
        $this->mock(\App\Services\Health\HealthCheckService::class, function ($mock) {
            $mock->shouldReceive('performChecks')
                ->andReturn([
                    'status' => 'unhealthy',
                    'checks' => [
                        'db_landlord' => 'healthy',
                        'db_tenant' => 'skipped',
                        'redis' => 'unhealthy',
                        'horizon' => 'skipped',
                        'storage' => 'healthy'
                    ],
                    'timestamp' => now()->toISOString()
                ]);
        });

        $response = $this->get('/healthz');

        $response->assertStatus(503);
        
        $data = $response->json();
        $this->assertEquals('unhealthy', $data['status']);
        $this->assertEquals('unhealthy', $data['checks']['redis']);
    }

    /**
     * Test health check with active tenant
     */
    public function test_health_check_with_active_tenant(): void
    {
        // Mock the health check service to return healthy status with tenant
        $this->mock(\App\Services\Health\HealthCheckService::class, function ($mock) {
            $mock->shouldReceive('performChecks')
                ->andReturn([
                    'status' => 'healthy',
                    'checks' => [
                        'db_landlord' => 'healthy',
                        'db_tenant' => 'healthy',
                        'redis' => 'healthy',
                        'horizon' => 'skipped',
                        'storage' => 'healthy'
                    ],
                    'timestamp' => now()->toISOString()
                ]);
        });

        $response = $this->get('/healthz');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals('healthy', $data['status']);
        $this->assertEquals('healthy', $data['checks']['db_landlord']);
        $this->assertEquals('healthy', $data['checks']['db_tenant']);
    }

    /**
     * Test rate limiting on health check endpoint
     */
    public function test_health_check_has_rate_limiting(): void
    {
        // Mock the health check service for rate limiting test
        $this->mock(\App\Services\Health\HealthCheckService::class, function ($mock) {
            $mock->shouldReceive('performChecks')
                ->andReturn([
                    'status' => 'healthy',
                    'checks' => [
                        'db_landlord' => 'healthy',
                        'db_tenant' => 'skipped',
                        'redis' => 'healthy',
                        'horizon' => 'skipped',
                        'storage' => 'healthy'
                    ],
                    'timestamp' => now()->toISOString()
                ]);
        });

        // Make 31 requests (limit is 30 per minute)
        for ($i = 0; $i < 31; $i++) {
            $response = $this->get('/healthz');
            
            if ($i < 30) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test health check response format
     */
    public function test_health_check_response_format(): void
    {
        // Mock the health check service
        $this->mock(\App\Services\Health\HealthCheckService::class, function ($mock) {
            $mock->shouldReceive('performChecks')
                ->andReturn([
                    'status' => 'healthy',
                    'checks' => [
                        'db_landlord' => 'healthy',
                        'db_tenant' => 'skipped',
                        'redis' => 'healthy',
                        'horizon' => 'skipped',
                        'storage' => 'healthy'
                    ],
                    'timestamp' => now()->toISOString()
                ]);
        });

        $response = $this->get('/healthz');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy',
            'checks' => [
                'db_landlord' => 'healthy',
                'db_tenant' => 'skipped',
                'redis' => 'healthy',
                'horizon' => 'skipped',
                'storage' => 'healthy'
            ]
        ]);

        // Verify timestamp is ISO8601 format
        $data = $response->json();
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            $data['timestamp']
        );
    }

    /**
     * Test health check with storage failure
     */
    public function test_health_check_storage_failure(): void
    {
        // Mock the health check service to return unhealthy status
        $this->mock(\App\Services\Health\HealthCheckService::class, function ($mock) {
            $mock->shouldReceive('performChecks')
                ->andReturn([
                    'status' => 'unhealthy',
                    'checks' => [
                        'db_landlord' => 'healthy',
                        'db_tenant' => 'skipped',
                        'redis' => 'healthy',
                        'horizon' => 'skipped',
                        'storage' => 'unhealthy'
                    ],
                    'timestamp' => now()->toISOString()
                ]);
        });

        $response = $this->get('/healthz');

        $response->assertStatus(503);
        
        $data = $response->json();
        $this->assertEquals('unhealthy', $data['status']);
        $this->assertEquals('unhealthy', $data['checks']['storage']);
    }

    /**
     * Test health check returns 503 when DB landlord fails
     */
    public function test_health_check_returns_503_when_db_landlord_fails(): void
    {
        // Mock the health check service to return unhealthy status
        $this->mock(\App\Services\Health\HealthCheckService::class, function ($mock) {
            $mock->shouldReceive('performChecks')
                ->andReturn([
                    'status' => 'unhealthy',
                    'checks' => [
                        'db_landlord' => 'unhealthy',
                        'db_tenant' => 'skipped',
                        'redis' => 'healthy',
                        'horizon' => 'skipped',
                        'storage' => 'healthy'
                    ],
                    'version' => [
                        'app' => '0.1.0',
                        'git_sha' => 'unknown'
                    ],
                    'timestamp' => now()->toISOString()
                ]);
        });

        $response = $this->get('/healthz');

        $response->assertStatus(503);
        
        $data = $response->json();
        $this->assertEquals('unhealthy', $data['status']);
        $this->assertEquals('unhealthy', $data['checks']['db_landlord']);
        
        // Should still include X-Request-Id header even when unhealthy
        $this->assertTrue($response->headers->has('X-Request-Id'), 'Response should include X-Request-Id header even when unhealthy');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
