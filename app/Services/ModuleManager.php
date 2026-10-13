<?php

namespace App\Services;

use Modules\Organization\Models\TenantModule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class ModuleManager
{
    protected string $modulesPath;
    protected array $discoveredModules = [];
    protected array $loadedProviders = [];

    public function __construct()
    {
        $this->modulesPath = base_path('app/Modules');
        $this->discoverModules();
    }

    /**
     * Get absolute path to modules directory.
     */
    public function getModulesPath(): string
    {
        if (!File::exists($this->modulesPath)) {
            File::makeDirectory($this->modulesPath, 0755, true);
        }
        return $this->modulesPath;
    }

    /**
     * Scan modules folder and parse module.json manifests.
     */
    public function discoverModules(): array
    {
        $this->discoveredModules = [];
        $modulesDir = $this->getModulesPath();

        if (!File::isDirectory($modulesDir)) {
            return [];
        }

        $directories = File::directories($modulesDir);

        foreach ($directories as $dir) {
            $manifestPath = $dir . DIRECTORY_SEPARATOR . 'module.json';
            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);
                if ($manifest && isset($manifest['slug'])) {
                    $slug = $manifest['slug'];
                    $manifest['path'] = $dir;
                    $manifest['is_active'] = $this->isModuleActiveForCurrentTenant($slug);
                    $this->discoveredModules[$slug] = $manifest;
                }
            }
        }

        return $this->discoveredModules;
    }

    /**
     * Check if module is active for the current tenant.
     */
    public function isModuleActiveForCurrentTenant(string $slug): bool
    {
        $tenantId = TenantContext::getTenantId();

        if (!$tenantId) {
            return true; // Central/Global administration default
        }

        $record = TenantModule::where('tenant_id', $tenantId)
            ->where('module_slug', $slug)
            ->first();

        // If not explicitly disabled, modules with auto_enable or default true are active
        return $record ? $record->is_enabled : true;
    }

    /**
     * Boot and register all active modules into Laravel.
     */
    public function bootActiveModules(): void
    {
        foreach ($this->discoveredModules as $slug => $module) {
            if ($module['is_active']) {
                $this->bootModule($module);
            }
        }
    }

    /**
     * Boot a single module.
     */
    protected function bootModule(array $module): void
    {
        $modulePath = $module['path'];
        $slug = $module['slug'];

        // 1. Register PSR-4 Autoloading dynamically if not registered via composer
        $namespace = 'Modules\\' . str_replace('-', '', ucwords($slug, '-')) . '\\';
        $srcPath = $modulePath . DIRECTORY_SEPARATOR . 'src';
        if (File::isDirectory($srcPath)) {
            spl_autoload_register(function ($class) use ($namespace, $srcPath) {
                if (str_starts_with($class, $namespace)) {
                    $relativeClass = substr($class, strlen($namespace));
                    $file = $srcPath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
                    if (File::exists($file)) {
                        require_once $file;
                    }
                }
            });
        }

        // 2. Register Service Providers
        if (!empty($module['providers'])) {
            foreach ($module['providers'] as $providerClass) {
                if (class_exists($providerClass) && !in_array($providerClass, $this->loadedProviders)) {
                    app()->register($providerClass);
                    $this->loadedProviders[] = $providerClass;
                }
            }
        }

        // 3. Register Views
        $viewsPath = $modulePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
        if (File::isDirectory($viewsPath)) {
            View::addNamespace("module-{$slug}", $viewsPath);
            View::addNamespace($slug, $viewsPath);
        }

        // 4. Register Routes
        $webRoutes = $modulePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        if (File::exists($webRoutes)) {
            Route::middleware(['web', \App\Http\Middleware\TenantMiddleware::class])
                ->group($webRoutes);
        }

        $apiRoutes = $modulePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
        if (File::exists($apiRoutes)) {
            Route::prefix('api/modules/' . $slug)
                ->middleware(['api', \App\Http\Middleware\TenantMiddleware::class])
                ->group($apiRoutes);
        }

        // 5. Run module init file if present
        $initFile = $modulePath . DIRECTORY_SEPARATOR . 'init.php';
        if (File::exists($initFile)) {
            require $initFile;
        }

    }

    /**
     * Get all discovered modules.
     */
    public function getAllModules(): array
    {
        return $this->discoveredModules;
    }

    /**
     * Get dynamic menu items from all active modules.
     */
    public function getModuleMenuItems(): array
    {
        $menus = [];
        foreach ($this->discoveredModules as $slug => $module) {
            if ($module['is_active'] && !empty($module['menu'])) {
                $menus[] = array_merge($module['menu'], [
                    'module_slug' => $slug,
                ]);
            }
        }

        usort($menus, fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        return $menus;
    }

    /**
     * Toggle module status for a tenant.
     */
    public function toggleModule(string $slug, bool $enable, ?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? TenantContext::getTenantId();
        if (!$tenantId) {
            return false;
        }

        TenantModule::updateOrCreate([
            'tenant_id' => $tenantId,
            'module_slug' => $slug,
        ], [
            'is_enabled' => $enable,
        ]);

        return true;
    }
}
