<?php

namespace Modules\Assets\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Assets\Models\AssetCategory;
use Modules\Assets\Models\Asset;
use Modules\Assets\Contracts\AssetCategoryRepositoryInterface;
use Modules\Assets\Contracts\AssetRepositoryInterface;
use Modules\Assets\Repositories\EloquentAssetCategoryRepository;
use Modules\Assets\Repositories\EloquentAssetRepository;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AssetCategoryRepositoryInterface::class, EloquentAssetCategoryRepository::class);
        $this->app->bind(AssetRepositoryInterface::class, EloquentAssetRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'assets');
            $this->loadViewsFrom($viewsPath, 'assets');
        }
    }
}
