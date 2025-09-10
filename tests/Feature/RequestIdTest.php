<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestIdTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that each request gets a unique X-Request-Id header
     */
    public function test_each_request_gets_unique_request_id(): void
    {
        $response1 = $this->get('/healthz');
        $response2 = $this->get('/healthz');

        // Both responses should have X-Request-Id headers
        $this->assertTrue($response1->headers->has('X-Request-Id'), 'First response should have X-Request-Id header');
        $this->assertTrue($response2->headers->has('X-Request-Id'), 'Second response should have X-Request-Id header');

        $requestId1 = $response1->headers->get('X-Request-Id');
        $requestId2 = $response2->headers->get('X-Request-Id');

        // Request IDs should be different
        $this->assertNotEquals($requestId1, $requestId2, 'Each request should have a unique X-Request-Id');
        
        // Request IDs should be valid UUIDs
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $requestId1,
            'X-Request-Id should be a valid UUID v4'
        );
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $requestId2,
            'X-Request-Id should be a valid UUID v4'
        );
    }

    /**
     * Test that incoming X-Request-Id header is respected
     */
    public function test_incoming_request_id_is_respected(): void
    {
        $incomingRequestId = '550e8400-e29b-41d4-a716-446655440000';
        
        $response = $this->withHeaders([
            'X-Request-Id' => $incomingRequestId
        ])->get('/healthz');

        $this->assertTrue($response->headers->has('X-Request-Id'), 'Response should have X-Request-Id header');
        
        $responseRequestId = $response->headers->get('X-Request-Id');
        $this->assertEquals($incomingRequestId, $responseRequestId, 'Response should use the incoming X-Request-Id');
    }

    /**
     * Test that X-Request-Id is present in all responses
     */
    public function test_request_id_present_in_all_responses(): void
    {
        // Test multiple health check requests to ensure X-Request-Id is consistent
        $routes = ['/healthz'];
        
        foreach ($routes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                $response->headers->has('X-Request-Id'), 
                "Route {$route} should include X-Request-Id header"
            );
        }
    }
}
