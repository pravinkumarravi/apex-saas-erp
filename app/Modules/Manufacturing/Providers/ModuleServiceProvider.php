<?php

namespace Modules\Manufacturing\Providers;

use Modules\Manufacturing\Contracts\BomRepositoryInterface;
use Modules\Manufacturing\Contracts\ProductionOrderRepositoryInterface;
use Modules\Manufacturing\Contracts\WorkCenterRepositoryInterface;
use Modules\Manufacturing\Models\BillOfMaterial;
use Modules\Manufacturing\Models\BomItem;
use Modules\Manufacturing\Models\ProductionMaterialConsumption;
use Modules\Manufacturing\Models\ProductionOrder;
use Modules\Manufacturing\Models\QualityInspection;
use Modules\Manufacturing\Models\WorkCenter;
use Modules\Manufacturing\Repositories\EloquentBomRepository;
use Modules\Manufacturing\Repositories\EloquentProductionOrderRepository;
use Modules\Manufacturing\Repositories\EloquentWorkCenterRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BomRepositoryInterface::class, EloquentBomRepository::class);
        $this->app->bind(ProductionOrderRepositoryInterface::class, EloquentProductionOrderRepository::class);
        $this->app->bind(WorkCenterRepositoryInterface::class, EloquentWorkCenterRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'manufacturing');
            $this->loadViewsFrom($viewsPath, 'manufacturing');
        }
    }
}
