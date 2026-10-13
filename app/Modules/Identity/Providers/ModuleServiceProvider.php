<?php

namespace Modules\Identity\Providers;

use Modules\Identity\Contracts\RoleRepositoryInterface;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Identity\Repositories\EloquentRoleRepository;
use Modules\Identity\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'identity');
            $this->loadViewsFrom($viewsPath, 'identity');
        }
    }
}
