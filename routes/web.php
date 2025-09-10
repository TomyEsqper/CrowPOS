<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landlord Routes (Central Application)
|--------------------------------------------------------------------------
|
| Here is where you can register landlord routes for your application. These
| routes are loaded by the RouteServiceProvider and are used for the
| central application (superadmin panel).
|
*/

// Landlord Dashboard
Route::get('/', function () {
    return view('landlord.welcome');
})->name('landlord.dashboard');

// Tenant Management Routes
Route::prefix('tenants')->name('tenants.')->group(function () {
    Route::get('/', function () {
        return view('landlord.tenants.index');
    })->name('index');
    
    Route::get('/create', function () {
        return view('landlord.tenants.create');
    })->name('create');
    
    Route::get('/{tenant}', function ($tenant) {
        return view('landlord.tenants.show', compact('tenant'));
    })->name('show');
});

// Features Management Routes
Route::prefix('features')->name('features.')->group(function () {
    Route::get('/', function () {
        return view('landlord.features.index');
    })->name('index');
});

// Domains Management Routes
Route::prefix('domains')->name('domains.')->group(function () {
    Route::get('/', function () {
        return view('landlord.domains.index');
    })->name('index');
});

// Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', function () {
        return view('landlord.reports.index');
    })->name('index');
    
    Route::get('/tenants', function () {
        return view('landlord.reports.tenants');
    })->name('tenants');
    
    Route::get('/usage', function () {
        return view('landlord.reports.usage');
    })->name('usage');
});
