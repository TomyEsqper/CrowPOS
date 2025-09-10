<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class FilamentAccessRestrictionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Filament admin panel is accessible on main domain
     */
    public function test_filament_accessible_on_main_domain(): void
    {
        $response = $this->get('/crowPOS');

        // Should not be 403 (access denied)
        $response->assertStatus(200);
    }

    /**
     * Test that Filament admin panel is blocked on tenant subdomains
     */
    public function test_filament_blocked_on_tenant_subdomains(): void
    {
        // Create a tenant
        $tenant = Tenant::create([
            'id' => 'test-tenant',
            'data' => ['name' => 'Test Tenant'],
        ]);

        Domain::create([
            'domain' => 'tenant.localhost',
            'tenant_id' => $tenant->id,
        ]);

        // Try to access Filament on tenant subdomain
        $response = $this->get('http://tenant.localhost/crowPOS');

        // Should be 403 (access denied)
        $response->assertStatus(403);
    }

    /**
     * Test that Filament admin panel is blocked when in tenant context
     */
    public function test_filament_blocked_in_tenant_context(): void
    {
        // Create a tenant
        $tenant = Tenant::create([
            'id' => 'test-tenant',
            'data' => ['name' => 'Test Tenant'],
        ]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        try {
            $response = $this->get('/crowPOS');
            
            // Should be 403 (access denied)
            $response->assertStatus(403);
        } finally {
            tenancy()->end();
        }
    }
}
