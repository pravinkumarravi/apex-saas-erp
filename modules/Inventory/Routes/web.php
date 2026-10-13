<?php

use Modules\Inventory\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/products', [InventoryController::class, 'products'])->name('products');
    Route::post('/products', [InventoryController::class, 'storeProduct'])->name('products.store');

    Route::get('/stock-levels', [InventoryController::class, 'stockLevels'])->name('stock-levels');
    Route::post('/adjust-stock', [InventoryController::class, 'adjustStock'])->name('adjust-stock');

    Route::get('/stock-movements', [InventoryController::class, 'stockMovements'])->name('stock-movements');

    Route::get('/warehouses', [InventoryController::class, 'warehouses'])->name('warehouses');
    Route::post('/warehouses', [InventoryController::class, 'storeWarehouse'])->name('warehouses.store');
});
