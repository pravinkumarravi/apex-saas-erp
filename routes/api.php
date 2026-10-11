<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'tenant' => \App\Services\TenantContext::getTenant()?->name ?? 'Central System',
        'subdomain' => \App\Services\TenantContext::getTenant()?->subdomain ?? 'apex',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Authentication Endpoint for Mobile Apps
Route::post('/auth/login', [ApiController::class, 'login']);

// Protected API Routes (Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [ApiController::class, 'profile']);
    Route::get('/products', [ApiController::class, 'products']);
    Route::get('/inventory/stocks', [ApiController::class, 'stockLevels']);
    Route::get('/procurement/orders', [ApiController::class, 'purchaseOrders']);
    Route::get('/sales/orders', [ApiController::class, 'salesOrders']);
    Route::get('/finance/invoices', [ApiController::class, 'invoices']);
    Route::get('/approvals/pending', [ApiController::class, 'pendingApprovals']);
    Route::post('/approvals/{id}/action', [ApiController::class, 'actionApproval']);
});
