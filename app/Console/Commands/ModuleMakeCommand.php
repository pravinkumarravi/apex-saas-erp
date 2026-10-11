<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make {name : The name of the module (e.g. QualityAudit, AssetTracker)}';
    protected $description = 'Scaffold a new DDD-ready, zip-installable ERP module';

    public function handle(): int
    {
        $rawName = $this->argument('name');
        $studly = Str::studly($rawName);
        $slug = Str::kebab($rawName);
        $modulePath = base_path("modules/{$studly}");

        if (File::isDirectory($modulePath)) {
            $this->error("Module directory already exists at: {$modulePath}");
            return self::FAILURE;
        }

        $this->info("Creating boilerplate for module [{$studly}] ({$slug})...");

        // Directories
        File::makeDirectory("{$modulePath}/src/Http/Controllers", 0755, true);
        File::makeDirectory("{$modulePath}/src/Models", 0755, true);
        File::makeDirectory("{$modulePath}/src/Repositories/Contracts", 0755, true);
        File::makeDirectory("{$modulePath}/src/Providers", 0755, true);
        File::makeDirectory("{$modulePath}/routes", 0755, true);
        File::makeDirectory("{$modulePath}/resources/views", 0755, true);
        File::makeDirectory("{$modulePath}/database/migrations", 0755, true);

        // 1. module.json
        $manifest = [
            'name' => $studly,
            'slug' => $slug,
            'version' => '1.0.0',
            'description' => "Modular {$studly} extension for ApexERP",
            'author' => 'Module Developer',
            'providers' => [
                "Modules\\{$studly}\\Providers\\{$studly}ServiceProvider"
            ],
            'menu' => [
                'name' => Str::headline($rawName),
                'icon' => 'settings',
                'route' => "modules.{$slug}.index",
                'order' => 80
            ]
        ];
        File::put("{$modulePath}/module.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 2. Service Provider
        $providerCode = <<<PHP
<?php

namespace Modules\\{$studly}\\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\\{$studly}\\Repositories\\Contracts\\{$studly}RepositoryInterface;
use Modules\\{$studly}\\Repositories\\{$studly}Repository;

class {$studly}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind({$studly}RepositoryInterface::class, {$studly}Repository::class);
    }

    public function boot(): void
    {
        // Loaded by ModuleManager
    }
}
PHP;
        File::put("{$modulePath}/src/Providers/{$studly}ServiceProvider.php", $providerCode);

        // 3. Controller
        $controllerCode = <<<PHP
<?php

namespace Modules\\{$studly}\\Http\\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\\{$studly}\\Repositories\\Contracts\\{$studly}RepositoryInterface;

class {$studly}Controller extends Controller
{
    protected {$studly}RepositoryInterface \$repository;

    public function __construct({$studly}RepositoryInterface \$repository)
    {
        \$this->repository = \$repository;
    }

    public function index()
    {
        \$items = \$this->repository->paginate(15);
        return view("module-{$slug}::index", compact('items'));
    }
}
PHP;
        File::put("{$modulePath}/src/Http/Controllers/{$studly}Controller.php", $controllerCode);

        // 4. Routes
        $routesCode = <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use Modules\\{$studly}\\Http\\Controllers\\{$studly}Controller;

Route::get('/modules/{$slug}', [{$studly}Controller::class, 'index'])->name('modules.{$slug}.index');
PHP;
        File::put("{$modulePath}/routes/web.php", $routesCode);

        // 5. Blade View
        $viewCode = <<<BLADE
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-erp.page-header 
        title="{$studly} Management" 
        subtitle="Custom modular application component with decoupled repository pattern."
        badge="Module Active"
    />

    <x-common.component-card title="{$studly} Records">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            This view is rendered from <code class="font-mono text-brand-600">modules/{$studly}/resources/views/index.blade.php</code>.
        </p>
    </x-common.component-card>
</div>
@endsection
BLADE;
        File::put("{$modulePath}/resources/views/index.blade.php", $viewCode);

        $this->info("✔ Module [{$studly}] scaffolded successfully in [modules/{$studly}]!");
        return self::SUCCESS;
    }
}
