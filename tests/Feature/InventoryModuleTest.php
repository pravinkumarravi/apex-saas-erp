<?php

namespace Tests\Feature;

use Modules\Organization\Domain\Models\Tenant;
use Modules\Identity\Domain\Models\User;
use Modules\Inventory\Application\Contracts\InventoryServiceInterface;
use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Domain\Contracts\ProductRepositoryInterface;
use Modules\Inventory\Domain\Contracts\StockRepositoryInterface;
use Modules\Inventory\Domain\Contracts\WarehouseRepositoryInterface;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Models\Warehouse;
use App\Services\TenantContext;
use Tests\TestCase;

class InventoryModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::where('subdomain', 'companya')->first();
        TenantContext::setTenant($tenant);
        $user = User::where('tenant_id', $tenant->id)->first();
        $this->actingAs($user);
    }

    public function test_inventory_repositories_resolve_from_container(): void
    {
        $productRepo = app(ProductRepositoryInterface::class);
        $warehouseRepo = app(WarehouseRepositoryInterface::class);
        $stockRepo = app(StockRepositoryInterface::class);
        $inventoryService = app(InventoryServiceInterface::class);

        $this->assertNotNull($productRepo);
        $this->assertNotNull($warehouseRepo);
        $this->assertNotNull($stockRepo);
        $this->assertNotNull($inventoryService);
    }

    public function test_inventory_service_handles_stock_adjustment_via_dto(): void
    {
        /** @var Product $product */
        $product = Product::first();
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::first();

        /** @var InventoryServiceInterface $service */
        $service = app(InventoryServiceInterface::class);

        $dto = new StockAdjustmentDTO(
            productId: $product->id,
            warehouseId: $warehouse->id,
            type: 'adjustment_in',
            quantity: 25.0,
            unitCost: 100.0,
            notes: 'Pilot module integration test adjustment',
            userId: auth()->id(),
        );

        $levelBefore = app(StockRepositoryInterface::class)->getOrCreateStockLevel($product->id, $warehouse->id);
        $qtyBefore = (float) $levelBefore->quantity_available;

        $updatedLevel = $service->adjustStock($dto);

        $this->assertEquals($qtyBefore + 25.0, (float) $updatedLevel->quantity_available);
        $this->assertTrue($service->hasAvailableStock($product->id, $warehouse->id, 25.0));
    }
}
