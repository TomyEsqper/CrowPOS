<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\Cache;

class TenantCachePrefixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that different tenants have different cache prefixes
     */
    public function test_tenants_have_different_cache_prefixes(): void
    {
        // Create two tenants
        $tenant1 = Tenant::create([
            'id' => 'tenant-1',
            'data' => ['name' => 'Tenant 1'],
        ]);

        $tenant2 = Tenant::create([
            'id' => 'tenant-2',
            'data' => ['name' => 'Tenant 2'],
        ]);

        Domain::create([
            'domain' => 'tenant1.localhost',
            'tenant_id' => $tenant1->id,
        ]);

        Domain::create([
            'domain' => 'tenant2.localhost',
            'tenant_id' => $tenant2->id,
        ]);

        // Make requests to different tenant domains
        $response1 = $this->get('http://tenant1.localhost/');
        $response2 = $this->get('http://tenant2.localhost/');

        // Both requests should succeed
        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Test cache prefix configuration
        $this->assertStringContains('tenant_1_cache', config('cache.prefix'));
    }

    /**
     * Test that cache keys are prefixed correctly for tenants
     */
    public function test_cache_keys_are_prefixed_by_tenant(): void
    {
        // Create a tenant
        $tenant = Tenant::create([
            'id' => 'test-tenant',
            'data' => ['name' => 'Test Tenant'],
        ]);

        Domain::create([
            'domain' => 'test.localhost',
            'tenant_id' => $tenant->id,
        ]);

        // Make request to tenant domain
        $response = $this->get('http://test.localhost/');
        $response->assertStatus(200);

        // Verify cache prefix is set
        $prefix = config('cache.prefix');
        $this->assertStringContains('tenant_test-tenant_cache', $prefix);
    }

    /**
     * Test that landlord (non-tenant) requests don't have tenant cache prefix
     */
    public function test_landlord_requests_dont_have_tenant_cache_prefix(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Cache prefix should not contain tenant prefix
        $prefix = config('cache.prefix');
        $this->assertStringNotContains('tenant_', $prefix);
    }
}
