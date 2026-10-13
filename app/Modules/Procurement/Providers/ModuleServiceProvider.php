<?php

namespace Modules\Procurement\Providers;

use Modules\Procurement\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Procurement\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Procurement\Contracts\SupplierRepositoryInterface;
use Modules\Procurement\Models\GoodsReceiptItem;
use Modules\Procurement\Models\GoodsReceiptNote;
use Modules\Procurement\Models\PurchaseOrder;
use Modules\Procurement\Models\PurchaseOrderItem;
use Modules\Procurement\Models\Supplier;
use Modules\Procurement\Repositories\EloquentGoodsReceiptRepository;
use Modules\Procurement\Repositories\EloquentPurchaseOrderRepository;
use Modules\Procurement\Repositories\EloquentSupplierRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SupplierRepositoryInterface::class, EloquentSupplierRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, EloquentPurchaseOrderRepository::class);
        $this->app->bind(GoodsReceiptRepositoryInterface::class, EloquentGoodsReceiptRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'procurement');
            $this->loadViewsFrom($viewsPath, 'procurement');
        }
    }
}
