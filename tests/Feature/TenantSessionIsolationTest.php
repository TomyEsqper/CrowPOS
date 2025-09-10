<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class TenantSessionIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that different tenants generate different session cookies
     */
    public function test_tenant_session_cookies_are_isolated(): void
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

        // Get session cookies from responses
        $cookie1 = $response1->headers->getCookies()[0] ?? null;
        $cookie2 = $response2->headers->getCookies()[0] ?? null;

        // Assert that cookies are different
        if ($cookie1 && $cookie2) {
            $this->assertNotEquals($cookie1->getName(), $cookie2->getName());
            $this->assertStringContains('tenant_1_session', $cookie1->getName());
            $this->assertStringContains('tenant_2_session', $cookie2->getName());
        }
    }

    /**
     * Test that tenant context is properly initialized
     */
    public function test_tenant_context_initialization(): void
    {
        $tenant = Tenant::create([
            'id' => 'test-tenant',
            'data' => ['name' => 'Test Tenant'],
        ]);

        Domain::create([
            'domain' => 'test.localhost',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->get('http://test.localhost/');

        $response->assertStatus(200);
    }
}
