<?php

namespace Modules\Projects\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectTask;
use Modules\Projects\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Contracts\ProjectTaskRepositoryInterface;
use Modules\Projects\Repositories\EloquentProjectRepository;
use Modules\Projects\Repositories\EloquentProjectTaskRepository;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
        $this->app->bind(ProjectTaskRepositoryInterface::class, EloquentProjectTaskRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'projects');
            $this->loadViewsFrom($viewsPath, 'projects');
        }
    }
}
