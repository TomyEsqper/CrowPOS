<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register tenant routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "tenant" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('tenant.welcome');
})->name('tenant.dashboard');

// POS Routes
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/', function () {
        return view('tenant.pos.index');
    })->name('index');
    
    Route::get('/sales', function () {
        return view('tenant.pos.sales');
    })->name('sales');
    
    Route::get('/products', function () {
        return view('tenant.pos.products');
    })->name('products');
});

// Inventory Routes
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', function () {
        return view('tenant.inventory.index');
    })->name('index');
    
    Route::get('/products', function () {
        return view('tenant.inventory.products');
    })->name('products');
    
    Route::get('/categories', function () {
        return view('tenant.inventory.categories');
    })->name('categories');
});

// Customers Routes
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', function () {
        return view('tenant.customers.index');
    })->name('index');
    
    Route::get('/accounts', function () {
        return view('tenant.customers.accounts');
    })->name('accounts');
});

// Cash Routes
Route::prefix('cash')->name('cash.')->group(function () {
    Route::get('/', function () {
        return view('tenant.cash.index');
    })->name('index');
    
    Route::get('/sessions', function () {
        return view('tenant.cash.sessions');
    })->name('sessions');
});

// Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', function () {
        return view('tenant.reports.index');
    })->name('index');
    
    Route::get('/sales', function () {
        return view('tenant.reports.sales');
    })->name('sales');
    
    Route::get('/inventory', function () {
        return view('tenant.reports.inventory');
    })->name('inventory');
});
