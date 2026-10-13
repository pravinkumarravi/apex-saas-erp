<?php

namespace Modules\Projects\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Modules\Projects\Domain\Models\Project;
use Modules\Projects\Domain\Models\ProjectTask;
use Modules\Projects\Domain\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Domain\Contracts\ProjectTaskRepositoryInterface;
use Modules\Projects\Infrastructure\Persistence\Eloquent\Repositories\EloquentProjectRepository;
use Modules\Projects\Infrastructure\Persistence\Eloquent\Repositories\EloquentProjectTaskRepository;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Project::class,
        ProjectTask::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
        $this->app->bind(ProjectTaskRepositoryInterface::class, EloquentProjectTaskRepository::class);
    }
}
