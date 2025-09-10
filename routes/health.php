<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes are used for health monitoring and should be accessible
| without authentication. They include rate limiting for security.
|
*/

Route::get('/healthz', [HealthController::class, 'index'])
    ->middleware('throttle:health')
    ->name('health.check');
