<?php

namespace Modules\Organization\Providers;

use Modules\Organization\Domain\Contracts\SubscriptionRepositoryInterface;
use Modules\Organization\Domain\Contracts\TenantRepositoryInterface;
use Modules\Organization\Domain\Models\Subscription;
use Modules\Organization\Domain\Models\Tenant;
use Modules\Organization\Domain\Models\TenantModule;
use Modules\Organization\Infrastructure\Persistence\Eloquent\Repositories\EloquentSubscriptionRepository;
use Modules\Organization\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantRepository;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Tenant::class,
        Subscription::class,
        TenantModule::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(TenantRepositoryInterface::class, EloquentTenantRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
    }
}
