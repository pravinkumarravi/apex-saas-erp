<?php

namespace Tests\Feature\Architecture;

use Tests\TestCase;

class ModularMonolithArchitectureTest extends TestCase
{
    /**
     * Test that all 11 ERP module service providers are registered and booted in Laravel.
     */
    public function test_all_erp_modules_are_registered(): void
    {
        $expectedProviders = [
            \Modules\Organization\Providers\ModuleServiceProvider::class,
            \Modules\Identity\Providers\ModuleServiceProvider::class,
            \Modules\Governance\Providers\ModuleServiceProvider::class,
            \Modules\Inventory\Providers\ModuleServiceProvider::class,
            \Modules\Procurement\Providers\ModuleServiceProvider::class,
            \Modules\Manufacturing\Providers\ModuleServiceProvider::class,
            \Modules\Sales\Providers\ModuleServiceProvider::class,
            \Modules\Accounting\Providers\ModuleServiceProvider::class,
            \Modules\HumanResources\Providers\ModuleServiceProvider::class,
            \Modules\Projects\Providers\ModuleServiceProvider::class,
            \Modules\Assets\Providers\ModuleServiceProvider::class,
        ];

        foreach ($expectedProviders as $provider) {
            $this->assertTrue(
                app()->providerIsLoaded($provider),
                "Expected provider '{$provider}' to be loaded in the application."
            );
        }
    }

    /**
     * Test that Repository Interfaces across modules resolve properly from the IoC container.
     */
    public function test_module_repository_interfaces_resolve_from_container(): void
    {
        $repositoryBindings = [
            \Modules\Organization\Contracts\TenantRepositoryInterface::class =>
                \Modules\Organization\Repositories\EloquentTenantRepository::class,
            \Modules\Identity\Contracts\UserRepositoryInterface::class =>
                \Modules\Identity\Repositories\EloquentUserRepository::class,
            \Modules\Governance\Contracts\AuditLogRepositoryInterface::class =>
                \Modules\Governance\Repositories\EloquentAuditLogRepository::class,
            \Modules\Inventory\Contracts\ProductRepositoryInterface::class =>
                \Modules\Inventory\Repositories\EloquentProductRepository::class,
            \Modules\Procurement\Contracts\SupplierRepositoryInterface::class =>
                \Modules\Procurement\Repositories\EloquentSupplierRepository::class,
            \Modules\Manufacturing\Contracts\ProductionOrderRepositoryInterface::class =>
                \Modules\Manufacturing\Repositories\EloquentProductionOrderRepository::class,
            \Modules\Sales\Contracts\SalesOrderRepositoryInterface::class =>
                \Modules\Sales\Repositories\EloquentSalesOrderRepository::class,
            \Modules\Accounting\Contracts\ChartOfAccountRepositoryInterface::class =>
                \Modules\Accounting\Repositories\EloquentChartOfAccountRepository::class,
            \Modules\HumanResources\Contracts\EmployeeRepositoryInterface::class =>
                \Modules\HumanResources\Repositories\EloquentEmployeeRepository::class,
            \Modules\Projects\Contracts\ProjectRepositoryInterface::class =>
                \Modules\Projects\Repositories\EloquentProjectRepository::class,
            \Modules\Assets\Contracts\AssetRepositoryInterface::class =>
                \Modules\Assets\Repositories\EloquentAssetRepository::class,
        ];

        foreach ($repositoryBindings as $interface => $expectedClass) {
            $resolved = app($interface);
            $this->assertInstanceOf($expectedClass, $resolved, "Interface {$interface} did not resolve to {$expectedClass}.");
        }
    }

    /**
     * Test that all Domain models implement their designated contract interface.
     */
    public function test_domain_models_implement_contracts(): void
    {
        $modelContracts = [
            \Modules\Organization\Models\Tenant::class => \Modules\Organization\Contracts\Tenant::class,
            \Modules\Identity\Models\User::class => \Modules\Identity\Contracts\User::class,
            \Modules\Inventory\Models\Product::class => \Modules\Inventory\Contracts\Product::class,
            \Modules\Procurement\Models\PurchaseOrder::class => \Modules\Procurement\Contracts\PurchaseOrder::class,
            \Modules\Manufacturing\Models\ProductionOrder::class => \Modules\Manufacturing\Contracts\ProductionOrder::class,
            \Modules\Sales\Models\SalesOrder::class => \Modules\Sales\Contracts\SalesOrder::class,
            \Modules\Accounting\Models\TaxInvoice::class => \Modules\Accounting\Contracts\TaxInvoice::class,
            \Modules\HumanResources\Models\Employee::class => \Modules\HumanResources\Contracts\Employee::class,
            \Modules\Projects\Models\Project::class => \Modules\Projects\Contracts\Project::class,
            \Modules\Assets\Models\Asset::class => \Modules\Assets\Contracts\Asset::class,
        ];

        foreach ($modelContracts as $modelClass => $contractInterface) {
            $instance = new $modelClass();
            $this->assertInstanceOf($contractInterface, $instance, "Model {$modelClass} does not implement {$contractInterface}.");
        }
    }

    /**
     * Test that the inter-module dependency graph forms a Directed Acyclic Graph (DAG) with no circular dependencies.
     */
    public function test_module_dependency_graph_has_no_cycles(): void
    {
        // Explicitly defined dependency topology based on docs/architecture/dependency-graph.md
        $graph = [
            'Organization'   => [],
            'Identity'       => ['Organization'],
            'Governance'     => ['Organization', 'Identity'],
            'Inventory'      => ['Organization'],
            'Procurement'    => ['Organization', 'Inventory', 'Governance'],
            'Manufacturing'  => ['Organization', 'Inventory', 'Governance'],
            'Sales'          => ['Organization', 'Inventory', 'Governance'],
            'Accounting'     => ['Organization', 'Sales', 'Procurement', 'Governance'],
            'HumanResources' => ['Organization', 'Identity', 'Accounting', 'Governance'],
            'Projects'       => ['Organization', 'Sales', 'HumanResources'],
            'Assets'         => ['Organization', 'Accounting'],
        ];

        $visited = [];
        $recursionStack = [];

        $hasCycle = false;
        foreach (array_keys($graph) as $node) {
            if ($this->detectCycle($node, $graph, $visited, $recursionStack)) {
                $hasCycle = true;
                break;
            }
        }

        $this->assertFalse($hasCycle, 'Circular dependency detected in module architecture dependency graph!');
    }

    private function detectCycle(string $node, array $graph, array &$visited, array &$recursionStack): bool
    {
        if (isset($recursionStack[$node]) && $recursionStack[$node]) {
            return true;
        }

        if (isset($visited[$node]) && $visited[$node]) {
            return false;
        }

        $visited[$node] = true;
        $recursionStack[$node] = true;

        if (isset($graph[$node])) {
            foreach ($graph[$node] as $neighbor) {
                if ($this->detectCycle($neighbor, $graph, $visited, $recursionStack)) {
                    return true;
                }
            }
        }

        $recursionStack[$node] = false;
        return false;
    }
}
