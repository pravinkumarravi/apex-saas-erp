<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\ProductionOrder;
use App\Models\BillOfMaterial;
use App\Models\Warehouse;
use App\Models\StockLevel;
use App\Services\TenantContext;

class ManufacturingWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::where('subdomain', 'companya')->first();
        TenantContext::setTenant($tenant);
        $user = User::where('tenant_id', $tenant->id)->first();
        $this->actingAs($user);
    }

    public function test_production_order_lifecycle_and_completion()
    {
        $tenant = TenantContext::getTenant();
        $bom = BillOfMaterial::with('items')->first();
        $sourceWh = Warehouse::first();
        $targetWh = Warehouse::skip(1)->first() ?? $sourceWh;

        $this->assertNotNull($bom, 'Seeded BOM should exist');
        $this->assertNotNull($sourceWh, 'Seeded warehouse should exist');

        // Provide sufficient raw material stock in source warehouse
        foreach ($bom->items as $item) {
            StockLevel::updateOrCreate([
                'tenant_id' => $tenant->id,
                'product_id' => $item->product_id,
                'warehouse_id' => $sourceWh->id,
            ], [
                'quantity_on_hand' => 500,
                'quantity_available' => 500,
                'quantity_reserved' => 0,
            ]);
        }

        // Create new Work Order
        $response = $this->post('/manufacturing/work-orders', [
            'bom_id' => $bom->id,
            'source_warehouse_id' => $sourceWh->id,
            'target_warehouse_id' => $targetWh->id,
            'target_quantity' => 1,
            'start_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order = ProductionOrder::latest()->first();
        $this->assertEquals(1, $order->target_quantity);
        $this->assertEquals('planned', $order->status);

        // Start Work Order run
        $responseStart = $this->post("/manufacturing/work-orders/{$order->id}/start");
        $responseStart->assertRedirect();
        $responseStart->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('in_progress', $order->status);

        // Complete Work Order run
        $responseComplete = $this->post("/manufacturing/work-orders/{$order->id}/complete", [
            'produced_quantity' => 1,
        ]);
        $responseComplete->assertRedirect();
        $responseComplete->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertEquals(1, $order->produced_quantity);
    }
}
