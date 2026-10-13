<?php

namespace Modules\Organization\Providers;

use Modules\Organization\Contracts\SubscriptionRepositoryInterface;
use Modules\Organization\Contracts\TenantRepositoryInterface;
use Modules\Organization\Models\Subscription;
use Modules\Organization\Models\Tenant;
use Modules\Organization\Models\TenantModule;
use Modules\Organization\Repositories\EloquentSubscriptionRepository;
use Modules\Organization\Repositories\EloquentTenantRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TenantRepositoryInterface::class, EloquentTenantRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'organization');
            $this->loadViewsFrom($viewsPath, 'organization');
        }
    }
}
