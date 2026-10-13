<?php

namespace Modules\Inventory\Providers;

use Modules\Inventory\Application\Contracts\InventoryServiceInterface;
use Modules\Inventory\Application\Services\InventoryService;
use Modules\Inventory\Contracts\ProductRepositoryInterface;
use Modules\Inventory\Contracts\StockRepositoryInterface;
use Modules\Inventory\Contracts\WarehouseRepositoryInterface;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductCategory;
use Modules\Inventory\Models\StockLevel;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\UnitOfMeasure;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Repositories\EloquentProductRepository;
use Modules\Inventory\Repositories\EloquentStockRepository;
use Modules\Inventory\Repositories\EloquentWarehouseRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, EloquentWarehouseRepository::class);
        $this->app->bind(StockRepositoryInterface::class, EloquentStockRepository::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'inventory');
            $this->loadViewsFrom($viewsPath, 'inventory');
        }
    }
}
