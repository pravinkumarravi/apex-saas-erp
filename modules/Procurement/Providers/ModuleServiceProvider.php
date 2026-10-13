<?php

namespace Modules\Procurement\Providers;

use Modules\Procurement\Domain\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Procurement\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Procurement\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Procurement\Domain\Models\GoodsReceiptItem;
use Modules\Procurement\Domain\Models\GoodsReceiptNote;
use Modules\Procurement\Domain\Models\PurchaseOrder;
use Modules\Procurement\Domain\Models\PurchaseOrderItem;
use Modules\Procurement\Domain\Models\Supplier;
use Modules\Procurement\Infrastructure\Persistence\Eloquent\Repositories\EloquentGoodsReceiptRepository;
use Modules\Procurement\Infrastructure\Persistence\Eloquent\Repositories\EloquentPurchaseOrderRepository;
use Modules\Procurement\Infrastructure\Persistence\Eloquent\Repositories\EloquentSupplierRepository;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Supplier::class,
        PurchaseOrder::class,
        PurchaseOrderItem::class,
        GoodsReceiptNote::class,
        GoodsReceiptItem::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(SupplierRepositoryInterface::class, EloquentSupplierRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, EloquentPurchaseOrderRepository::class);
        $this->app->bind(GoodsReceiptRepositoryInterface::class, EloquentGoodsReceiptRepository::class);
    }
}
