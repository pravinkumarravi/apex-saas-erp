<?php

// Module Initialization for Inventory & Storage

register_activation_hook('inventory', function () {
    // Initial activation tasks
});

add_action('admin_init', function () {
    // Menu or admin initialization
});

// Provide product options for external modules (e.g. Accounting)
add_filter('accounting_product_options', function ($default = null) {
    return \Modules\Inventory\Models\Product::where('is_active', true)->get();
});

// Provide finished goods and raw materials options for Manufacturing
add_filter('manufacturing_finished_goods_options', function ($default = null) {
    return \Modules\Inventory\Models\Product::whereIn('product_type', ['finished_good', 'work_in_progress'])->get();
});

add_filter('manufacturing_raw_materials_options', function ($default = null) {
    return \Modules\Inventory\Models\Product::whereIn('product_type', ['raw_material', 'work_in_progress', 'consumable'])->get();
});

add_filter('manufacturing_warehouse_options', function ($default = null) {
    return \Modules\Inventory\Models\Warehouse::where('is_active', true)->get();
});

add_filter('inventory_find_product', function ($default, $id) {
    return \Modules\Inventory\Models\Product::find($id);
}, 10, 2);

// Validate stock availability before a work order starts
add_filter('manufacturing_validate_materials', function ($isValid, $workOrder) {
    foreach ($workOrder->materialConsumptions as $consumption) {
        $stockLevel = \Modules\Inventory\Models\StockLevel::where('product_id', $consumption->product_id)
            ->where('warehouse_id', $workOrder->source_warehouse_id)
            ->first();

        $neededQty = (float) $consumption->planned_quantity;

        if (!$stockLevel || $stockLevel->quantity_available < $neededQty) {
            $productName = $consumption->product?->name ?? 'Raw Material';
            return "Insufficient stock for {$productName} in source warehouse.";
        }
    }
    return true;
}, 10, 2);

// Action: Issue raw materials when a work order starts
add_action('production_order_materials_issued', function ($workOrder) {
    foreach ($workOrder->materialConsumptions as $consumption) {
        $neededQty = (float) $consumption->planned_quantity;

        $stockLevel = \Modules\Inventory\Models\StockLevel::where('product_id', $consumption->product_id)
            ->where('warehouse_id', $workOrder->source_warehouse_id)
            ->first();

        if ($stockLevel) {
            $stockLevel->quantity_on_hand -= $neededQty;
            $stockLevel->quantity_available -= $neededQty;
            $stockLevel->save();
        }

        \Modules\Inventory\Models\StockMovement::create([
            'product_id' => $consumption->product_id,
            'warehouse_id' => $workOrder->source_warehouse_id,
            'movement_type' => 'production_issue',
            'reference_type' => 'production_order',
            'reference_id' => $workOrder->id,
            'quantity' => -$neededQty,
            'unit_cost' => $consumption->unit_cost,
            'total_cost' => $neededQty * (float) $consumption->unit_cost,
            'notes' => "Material issued for Work Order {$workOrder->order_number}",
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);
    }
});

// Action: Receive finished goods when a work order completes
add_action('production_order_completed', function ($workOrder, $producedQty) {
    $targetStock = \Modules\Inventory\Models\StockLevel::firstOrCreate([
        'tenant_id' => \App\Services\TenantContext::getTenantId(),
        'product_id' => $workOrder->product_id,
        'warehouse_id' => $workOrder->target_warehouse_id,
    ], [
        'quantity_on_hand' => 0,
        'quantity_reserved' => 0,
        'quantity_available' => 0,
    ]);

    $targetStock->quantity_on_hand += $producedQty;
    $targetStock->quantity_available += $producedQty;
    $targetStock->save();

    $product = \Modules\Inventory\Models\Product::find($workOrder->product_id);
    $unitCost = (float) ($product?->purchase_price ?? 0);

    \Modules\Inventory\Models\StockMovement::create([
        'product_id' => $workOrder->product_id,
        'warehouse_id' => $workOrder->target_warehouse_id,
        'movement_type' => 'production_receipt',
        'reference_type' => 'production_order',
        'reference_id' => $workOrder->id,
        'quantity' => $producedQty,
        'unit_cost' => $unitCost,
        'total_cost' => $producedQty * $unitCost,
        'batch_number' => 'MFG-' . date('ymd') . '-' . rand(100, 999),
        'notes' => "Finished goods received from Work Order {$workOrder->order_number}",
        'created_by' => \Illuminate\Support\Facades\Auth::id(),
    ]);
}, 10, 2);

// Filters: Provide options for Procurement module
add_filter('procurement_product_options', function ($default = null) {
    return \Modules\Inventory\Models\Product::where('is_active', true)->get();
});

add_filter('procurement_warehouse_options', function ($default = null) {
    return \Modules\Inventory\Models\Warehouse::where('is_active', true)->get();
});

// Action: Handle inward stock from Procurement GRN
add_action('procurement_goods_received', function ($grn, array $items) {
    foreach ($items as $item) {
        $acceptedQty = (float) $item['accepted_quantity'];
        if ($acceptedQty <= 0) continue;

        $stockLevel = \Modules\Inventory\Models\StockLevel::firstOrCreate([
            'tenant_id' => \App\Services\TenantContext::getTenantId(),
            'product_id' => $item['product_id'],
            'warehouse_id' => $grn->warehouse_id,
        ], [
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
            'quantity_available' => 0,
        ]);

        $stockLevel->quantity_on_hand += $acceptedQty;
        $stockLevel->quantity_available += $acceptedQty;
        $stockLevel->save();

        \Modules\Inventory\Models\StockMovement::create([
            'product_id' => $item['product_id'],
            'warehouse_id' => $grn->warehouse_id,
            'movement_type' => 'purchase_receipt',
            'reference_type' => 'goods_receipt_note',
            'reference_id' => $grn->id,
            'quantity' => $acceptedQty,
            'unit_cost' => $item['unit_price'],
            'total_cost' => $acceptedQty * (float) $item['unit_price'],
            'batch_number' => $item['batch_number'] ?? null,
            'notes' => "Inward receipt via GRN {$grn->grn_number} for PO " . ($grn->purchaseOrder?->po_number ?? ''),
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);
    }
}, 10, 2);

// Filters: Provide options for Sales module
add_filter('sales_product_options', function ($default = null) {
    return \Modules\Inventory\Models\Product::where('is_active', true)
        ->whereIn('product_type', ['finished_good', 'service'])
        ->get();
});

add_filter('sales_warehouse_options', function ($default = null) {
    return \Modules\Inventory\Models\Warehouse::where('is_active', true)->get();
});

// Validate stock for Sales delivery challan dispatch
add_filter('sales_validate_dispatch_stock', function ($isValid, $order, $warehouseId) {
    foreach ($order->items as $item) {
        $stockLevel = \Modules\Inventory\Models\StockLevel::where('product_id', $item->product_id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $neededQty = (float) $item->quantity;
        if (!$stockLevel || $stockLevel->quantity_available < $neededQty) {
            $prodName = $item->product?->name ?? 'Product';
            return "Insufficient stock for {$prodName} to dispatch.";
        }
    }
    return true;
}, 10, 3);

// Action: Handle stock deduction on Sales delivery dispatch
add_action('sales_delivery_challan_dispatched', function ($challan, $order) {
    foreach ($order->items as $item) {
        $neededQty = (float) $item->quantity;

        $stockLevel = \Modules\Inventory\Models\StockLevel::where('product_id', $item->product_id)
            ->where('warehouse_id', $challan->warehouse_id)
            ->first();

        if ($stockLevel) {
            $stockLevel->quantity_on_hand -= $neededQty;
            $stockLevel->quantity_available -= $neededQty;
            $stockLevel->save();
        }

        \Modules\Inventory\Models\StockMovement::create([
            'product_id' => $item->product_id,
            'warehouse_id' => $challan->warehouse_id,
            'movement_type' => 'sales_dispatch',
            'reference_type' => 'sales_order',
            'reference_id' => $order->id,
            'quantity' => -$neededQty,
            'unit_cost' => $item->unit_price,
            'total_cost' => $neededQty * (float) $item->unit_price,
            'notes' => "Dispatched under Delivery Challan {$challan->challan_number}",
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $item->update(['shipped_quantity' => $neededQty]);
    }
}, 10, 2);




