# Adding a New Module to ApexERP

## Step-by-Step Guide

Follow this guide to scaffold and register a new business module in the ERP:

### 1. Create the Module Directory Tree
Under `app/Modules/<ModuleName>/`:
- `Providers/ModuleServiceProvider.php`
- `Domain/Models/`
- `Domain/Contracts/`
- `Application/Contracts/`
- `Application/DTOs/`
- `Application/Services/`
- `Infrastructure/Persistence/Eloquent/Repositories/`
- `Http/Controllers/`
- `Routes/web.php` and `Routes/api.php`

### 2. Implement the `ModuleServiceProvider`
Extend `Konekt\Concord\BaseModuleServiceProvider`:
```php
namespace Modules\<ModuleName>\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        // Register Domain Models here
    ];

    public function register(): void
    {
        parent::register();

        // Bind Repository interfaces to Eloquent implementations
        $this->app->bind(
            \Modules\<ModuleName>\Domain\Contracts\<Model>RepositoryInterface::class,
            \Modules\<ModuleName>\Infrastructure\Persistence\Eloquent\Repositories\Eloquent<Model>Repository::class
        );
    }

    public function boot(): void
    {
        parent::boot();

        // Load Routes
        $webRoutes = __DIR__ . '/../Routes/web.php';
        if (file_exists($webRoutes)) {
            $this->loadRoutesFrom($webRoutes);
        }
    }
}
```

### 3. Register the Module in Concord
Add the provider to `config/concord.php`:
```php
'modules' => [
    // ...
    Modules\<ModuleName>\Providers\ModuleServiceProvider::class,
],
```

### 4. Create Repository Interface and Implementation
Define the interface in `Domain/Contracts/` and the concrete class in `Infrastructure/Persistence/Eloquent/Repositories/`.

### 5. Verify Architectural Integrity
Run the architecture test suite:
```bash
php artisan test --filter=ModularMonolithArchitectureTest
```
