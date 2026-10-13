<?php

namespace App\Providers;

use App\Services\HookManager;
use App\Services\MenuManager;
use App\Services\ModuleInstaller;
use App\Services\ModuleManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Load global hook helpers
        if (file_exists(app_path('Helpers/hooks.php'))) {
            require_once app_path('Helpers/hooks.php');
        }

        $this->app->singleton('hook', function () {
            return new HookManager();
        });

        $this->app->singleton(HookManager::class, function ($app) {
            return $app['hook'];
        });

        $this->app->singleton('app_menu', function () {
            return new MenuManager();
        });

        $this->app->singleton(MenuManager::class, function ($app) {
            return $app['app_menu'];
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
        // Register Hook Blade Directives
        Blade::directive('action', function ($expression) {
            return "<?php do_action({$expression}); ?>";
        });

        Blade::directive('filter', function ($expression) {
            return "<?php echo apply_filters({$expression}); ?>";
        });

        $moduleManager = $this->app->make(ModuleManager::class);
        $moduleManager->bootActiveModules();
    }
}
