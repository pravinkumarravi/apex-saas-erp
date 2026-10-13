<?php

use Illuminate\Support\Facades\Route;
use Modules\RestApi\Http\Controllers\RestApiController;
use Modules\RestApi\Http\Controllers\ApiKeyController;
use Modules\RestApi\Http\Controllers\WebhookController;

Route::prefix('developer/api')->name('rest-api.')->group(function () {
    Route::get('/', [RestApiController::class, 'dashboard'])->name('index');
    Route::get('/dashboard', [RestApiController::class, 'dashboard'])->name('dashboard');
    Route::get('/docs', [RestApiController::class, 'docs'])->name('docs');

    // API Keys Management
    Route::get('/keys', [ApiKeyController::class, 'index'])->name('keys.index');
    Route::post('/keys', [ApiKeyController::class, 'store'])->name('keys.store');
    Route::delete('/keys/{id}', [ApiKeyController::class, 'destroy'])->name('keys.destroy');

    // Webhooks Subscriptions
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::delete('/webhooks/{id}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{id}/test', [WebhookController::class, 'testPing'])->name('webhooks.test');
});
