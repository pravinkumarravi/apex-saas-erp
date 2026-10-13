<?php

namespace Modules\Assets\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Modules\Assets\Domain\Models\AssetCategory;
use Modules\Assets\Domain\Models\Asset;
use Modules\Assets\Domain\Contracts\AssetCategoryRepositoryInterface;
use Modules\Assets\Domain\Contracts\AssetRepositoryInterface;
use Modules\Assets\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetCategoryRepository;
use Modules\Assets\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetRepository;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        AssetCategory::class,
        Asset::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(AssetCategoryRepositoryInterface::class, EloquentAssetCategoryRepository::class);
        $this->app->bind(AssetRepositoryInterface::class, EloquentAssetRepository::class);
    }
}
