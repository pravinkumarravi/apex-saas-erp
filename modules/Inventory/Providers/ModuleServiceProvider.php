<?php

namespace Modules\Inventory\Providers;

use Modules\Inventory\Application\Contracts\InventoryServiceInterface;
use Modules\Inventory\Application\Services\InventoryService;
use Modules\Inventory\Domain\Contracts\ProductRepositoryInterface;
use Modules\Inventory\Domain\Contracts\StockRepositoryInterface;
use Modules\Inventory\Domain\Contracts\WarehouseRepositoryInterface;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Models\ProductCategory;
use Modules\Inventory\Domain\Models\StockLevel;
use Modules\Inventory\Domain\Models\StockMovement;
use Modules\Inventory\Domain\Models\UnitOfMeasure;
use Modules\Inventory\Domain\Models\Warehouse;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentWarehouseRepository;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Product::class,
        ProductCategory::class,
        UnitOfMeasure::class,
        Warehouse::class,
        StockLevel::class,
        StockMovement::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, EloquentWarehouseRepository::class);
        $this->app->bind(StockRepositoryInterface::class, EloquentStockRepository::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
    }
}
