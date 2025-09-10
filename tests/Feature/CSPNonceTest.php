<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CSPNonceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that each request returns a unique CSP nonce
     */
    public function test_each_request_has_unique_csp_nonce(): void
    {
        // Test with a simple route that doesn't use tenant functions
        $response1 = $this->get('/healthz');
        $response2 = $this->get('/healthz');

        // Get CSP headers
        $csp1 = $response1->headers->get('Content-Security-Policy');
        $csp2 = $response2->headers->get('Content-Security-Policy');

        // Extract nonces from CSP headers
        preg_match('/nonce-([a-f0-9]{32})/', $csp1, $matches1);
        preg_match('/nonce-([a-f0-9]{32})/', $csp2, $matches2);

        $nonce1 = $matches1[1] ?? null;
        $nonce2 = $matches2[1] ?? null;

        // Assert nonces exist and are different
        $this->assertNotNull($nonce1, 'First request should have CSP nonce');
        $this->assertNotNull($nonce2, 'Second request should have CSP nonce');
        $this->assertNotEquals($nonce1, $nonce2, 'Each request should have a unique nonce');
        $this->assertEquals(32, strlen($nonce1), 'Nonce should be 32 characters (16 bytes hex)');
        $this->assertEquals(32, strlen($nonce2), 'Nonce should be 32 characters (16 bytes hex)');
    }

    /**
     * Test that CSP header exists and contains required directives
     */
    public function test_csp_header_contains_required_directives(): void
    {
        $response = $this->get('/healthz');
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp, 'CSP header should be present');
        $this->assertStringContainsString('default-src \'self\'', $csp, 'CSP should include default-src directive');
        $this->assertStringContainsString('script-src', $csp, 'CSP should include script-src directive');
        $this->assertStringContainsString('style-src', $csp, 'CSP should include style-src directive');
        $this->assertStringContainsString('font-src', $csp, 'CSP should include font-src directive');
        $this->assertStringContainsString('frame-ancestors \'none\'', $csp, 'CSP should include frame-ancestors directive');
        $this->assertStringContainsString('nonce-', $csp, 'CSP should include nonce');
        $this->assertStringContainsString("'self'", $csp, 'CSP should include self directive');
        
        // Ensure unsafe directives are not present
        $this->assertStringNotContainsString('unsafe-inline', $csp, 'CSP should not include unsafe-inline');
        $this->assertStringNotContainsString('unsafe-eval', $csp, 'CSP should not include unsafe-eval');
    }

    /**
     * Test that csp_nonce() helper function works
     */
    public function test_csp_nonce_helper_function(): void
    {
        // The helper should be available
        $this->assertTrue(function_exists('csp_nonce'), 'csp_nonce helper should be available');
        
        // Test that we can call it (though it will be empty in test context)
        $nonce = csp_nonce();
        $this->assertIsString($nonce, 'csp_nonce() should return a string');
    }
}
