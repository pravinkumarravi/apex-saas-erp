<?php

namespace Tests\Feature\Architecture;

use Tests\TestCase;
use Konekt\Concord\Facades\Concord;

class ModularMonolithArchitectureTest extends TestCase
{
    /**
     * Test that all 11 ERP modules are registered and booted in Concord.
     */
    public function test_all_eleven_concord_modules_are_registered(): void
    {
        $expectedModules = [
            'organization',
            'identity',
            'governance',
            'inventory',
            'procurement',
            'manufacturing',
            'sales',
            'accounting',
            'human_resources',
            'projects',
            'assets',
        ];

        $registeredModules = collect(Concord::getModules())->map(fn ($m) => $m->getId())->toArray();

        foreach ($expectedModules as $module) {
            $this->assertContains(
                $module,
                $registeredModules,
                "Expected module '{$module}' to be registered in Concord."
            );
        }

        $this->assertGreaterThanOrEqual(11, count($registeredModules));
    }

    /**
     * Test that Repository Interfaces across modules resolve properly from the IoC container.
     */
    public function test_module_repository_interfaces_resolve_from_container(): void
    {
        $repositoryBindings = [
            \Modules\Organization\Domain\Contracts\TenantRepositoryInterface::class =>
                \Modules\Organization\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantRepository::class,
            \Modules\Identity\Domain\Contracts\UserRepositoryInterface::class =>
                \Modules\Identity\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository::class,
            \Modules\Governance\Domain\Contracts\AuditLogRepositoryInterface::class =>
                \Modules\Governance\Infrastructure\Persistence\Eloquent\Repositories\EloquentAuditLogRepository::class,
            \Modules\Inventory\Domain\Contracts\ProductRepositoryInterface::class =>
                \Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository::class,
            \Modules\Procurement\Domain\Contracts\SupplierRepositoryInterface::class =>
                \Modules\Procurement\Infrastructure\Persistence\Eloquent\Repositories\EloquentSupplierRepository::class,
            \Modules\Manufacturing\Domain\Contracts\ProductionOrderRepositoryInterface::class =>
                \Modules\Manufacturing\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductionOrderRepository::class,
            \Modules\Sales\Domain\Contracts\SalesOrderRepositoryInterface::class =>
                \Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories\EloquentSalesOrderRepository::class,
            \Modules\Accounting\Domain\Contracts\ChartOfAccountRepositoryInterface::class =>
                \Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentChartOfAccountRepository::class,
            \Modules\HumanResources\Domain\Contracts\EmployeeRepositoryInterface::class =>
                \Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmployeeRepository::class,
            \Modules\Projects\Domain\Contracts\ProjectRepositoryInterface::class =>
                \Modules\Projects\Infrastructure\Persistence\Eloquent\Repositories\EloquentProjectRepository::class,
            \Modules\Assets\Domain\Contracts\AssetRepositoryInterface::class =>
                \Modules\Assets\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetRepository::class,
        ];

        foreach ($repositoryBindings as $interface => $expectedClass) {
            $resolved = app($interface);
            $this->assertInstanceOf($expectedClass, $resolved, "Interface {$interface} did not resolve to {$expectedClass}.");
        }
    }

    /**
     * Test that all Concord models implement their designated contract interface.
     */
    public function test_concord_models_implement_contracts(): void
    {
        $modelContracts = [
            \Modules\Organization\Domain\Models\Tenant::class => \Modules\Organization\Domain\Contracts\Tenant::class,
            \Modules\Identity\Domain\Models\User::class => \Modules\Identity\Domain\Contracts\User::class,
            \Modules\Inventory\Domain\Models\Product::class => \Modules\Inventory\Domain\Contracts\Product::class,
            \Modules\Procurement\Domain\Models\PurchaseOrder::class => \Modules\Procurement\Domain\Contracts\PurchaseOrder::class,
            \Modules\Manufacturing\Domain\Models\ProductionOrder::class => \Modules\Manufacturing\Domain\Contracts\ProductionOrder::class,
            \Modules\Sales\Domain\Models\SalesOrder::class => \Modules\Sales\Domain\Contracts\SalesOrder::class,
            \Modules\Accounting\Domain\Models\TaxInvoice::class => \Modules\Accounting\Domain\Contracts\TaxInvoice::class,
            \Modules\HumanResources\Domain\Models\Employee::class => \Modules\HumanResources\Domain\Contracts\Employee::class,
            \Modules\Projects\Domain\Models\Project::class => \Modules\Projects\Domain\Contracts\Project::class,
            \Modules\Assets\Domain\Models\Asset::class => \Modules\Assets\Domain\Contracts\Asset::class,
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
