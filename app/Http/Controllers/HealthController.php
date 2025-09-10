<?php

namespace App\Http\Controllers;

use App\Services\Health\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __construct(
        private HealthCheckService $healthCheckService
    ) {}

    /**
     * Health check endpoint
     */
    public function index(Request $request): JsonResponse
    {
        $healthData = $this->healthCheckService->performChecks();
        
        $statusCode = $healthData['status'] === 'healthy' ? 200 : 503;
        
        $response = response()->json($healthData, $statusCode);
        
        // Ensure X-Request-Id header is present (set by RequestId middleware)
        $requestId = $request->attributes->get('request_id');
        if ($requestId) {
            $response->headers->set('X-Request-Id', $requestId);
        }
        
        return $response;
    }
}
