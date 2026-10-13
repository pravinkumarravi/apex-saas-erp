<?php

namespace Modules\Manufacturing\Providers;

use Modules\Manufacturing\Domain\Contracts\BomRepositoryInterface;
use Modules\Manufacturing\Domain\Contracts\ProductionOrderRepositoryInterface;
use Modules\Manufacturing\Domain\Contracts\WorkCenterRepositoryInterface;
use Modules\Manufacturing\Domain\Models\BillOfMaterial;
use Modules\Manufacturing\Domain\Models\BomItem;
use Modules\Manufacturing\Domain\Models\ProductionMaterialConsumption;
use Modules\Manufacturing\Domain\Models\ProductionOrder;
use Modules\Manufacturing\Domain\Models\QualityInspection;
use Modules\Manufacturing\Domain\Models\WorkCenter;
use Modules\Manufacturing\Infrastructure\Persistence\Eloquent\Repositories\EloquentBomRepository;
use Modules\Manufacturing\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductionOrderRepository;
use Modules\Manufacturing\Infrastructure\Persistence\Eloquent\Repositories\EloquentWorkCenterRepository;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        WorkCenter::class,
        BillOfMaterial::class,
        BomItem::class,
        ProductionOrder::class,
        ProductionMaterialConsumption::class,
        QualityInspection::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(BomRepositoryInterface::class, EloquentBomRepository::class);
        $this->app->bind(ProductionOrderRepositoryInterface::class, EloquentProductionOrderRepository::class);
        $this->app->bind(WorkCenterRepositoryInterface::class, EloquentWorkCenterRepository::class);
    }
}
