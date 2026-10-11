<?php

namespace App\Providers;

use App\Services\HookManager;
use App\Services\ModuleInstaller;
use App\Services\ModuleManager;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('hook', function () {
            return new HookManager();
        });

        $this->app->singleton(HookManager::class, function ($app) {
            return $app['hook'];
        });

        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager();
        });

        $this->app->singleton(ModuleInstaller::class, function ($app) {
            return new ModuleInstaller($app->make(ModuleManager::class));
        });
    }

    public function boot(): void
    {
        $moduleManager = $this->app->make(ModuleManager::class);
        $moduleManager->bootActiveModules();
    }
}
