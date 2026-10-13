<?php

use Illuminate\Support\Facades\Route;
use Modules\RestApi\Http\Controllers\RestApiController;
use Modules\RestApi\Http\Middleware\RequireApiKeyMiddleware;

// Public Health Check
Route::get('/health', [RestApiController::class, 'health'])->name('api.health');

// Protected by API Key Middleware
Route::middleware(RequireApiKeyMiddleware::class)->group(function () {
    Route::get('/resources', [RestApiController::class, 'resources'])->name('api.resources');

    // Sales API resources
    Route::get('/sales/orders', function () {
        return response()->json([
            'success' => true,
            'data' => \Modules\Sales\Models\SalesOrder::with(['customer', 'items'])->latest()->take(20)->get(),
        ]);
    });

    // CRM Customers API resources
    Route::get('/crm/customers', function () {
        return response()->json([
            'success' => true,
            'data' => \Modules\Sales\Models\Customer::latest()->take(20)->get(),
        ]);
    });

    // Inventory Products API resources
    Route::get('/inventory/products', function () {
        return response()->json([
            'success' => true,
            'data' => \Modules\Inventory\Models\Product::latest()->take(20)->get(),
        ]);
    });
});
