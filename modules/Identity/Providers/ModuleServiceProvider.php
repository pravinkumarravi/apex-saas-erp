<?php

namespace Modules\Identity\Providers;

use Modules\Identity\Domain\Contracts\RoleRepositoryInterface;
use Modules\Identity\Domain\Contracts\UserRepositoryInterface;
use Modules\Identity\Domain\Models\Permission;
use Modules\Identity\Domain\Models\Role;
use Modules\Identity\Domain\Models\User;
use Modules\Identity\Infrastructure\Persistence\Eloquent\Repositories\EloquentRoleRepository;
use Modules\Identity\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        User::class,
        Role::class,
        Permission::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
    }
}
