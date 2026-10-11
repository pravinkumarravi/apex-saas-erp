<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillOfMaterial;
use App\Models\BomItem;
use App\Models\Product;
use App\Models\ProductionMaterialConsumption;
use App\Models\ProductionOrder;
use App\Models\QualityInspection;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WorkCenter;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManufacturingController
{
    // --- BILLS OF MATERIAL (BOM) ---
    public function bom(Request $request)
    {
        $boms = BillOfMaterial::with(['product.category', 'items.product'])->latest()->paginate(10);
        $finishedGoods = Product::whereIn('product_type', ['finished_good', 'work_in_progress'])->get();
        $rawMaterials = Product::whereIn('product_type', ['raw_material', 'work_in_progress', 'consumable'])->get();

        return view('pages.manufacturing.bom', compact('boms', 'finishedGoods', 'rawMaterials'));
    }

    public function storeBom(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'bom_number' => 'required|string|max:50',
            'version' => 'required|string|max:10',
            'quantity' => 'required|numeric|min:0.0001',
            'routing_instructions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.scrap_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($request) {
            $estimatedCost = 0;
            $itemsData = [];

            foreach ($request->items as $itemInput) {
                $component = Product::findOrFail($itemInput['product_id']);
                $qty = (float) $itemInput['quantity'];
                $scrap = (float) ($itemInput['scrap_percentage'] ?? 0);
                $effectiveQty = $qty * (1 + ($scrap / 100));
                $cost = (float) $component->purchase_price;
                $lineCost = $effectiveQty * $cost;

                $estimatedCost += $lineCost;

                $itemsData[] = [
                    'product_id' => $component->id,
                    'quantity' => $qty,
                    'scrap_percentage' => $scrap,
                    'unit_cost' => $cost,
                ];
            }

            $bom = BillOfMaterial::create([
                'product_id' => $request->product_id,
                'bom_number' => $request->bom_number,
                'version' => $request->version,
                'quantity' => $request->quantity,
                'estimated_cost' => $estimatedCost,
                'routing_instructions' => $request->routing_instructions,
                'is_active' => true,
            ]);

            foreach ($itemsData as $itemData) {
                $bom->items()->create($itemData);
            }

            AuditLog::log('created', $bom, null, $bom->toArray());

            return redirect()->back()->with('success', "Bill of Materials {$bom->bom_number} created successfully.");
        });
    }

    // --- PRODUCTION ORDERS (WORK ORDERS) ---
    public function workOrders(Request $request)
    {
        $workOrders = ProductionOrder::with([
            'product',
            'bom',
            'sourceWarehouse',
            'targetWarehouse',
            'materialConsumptions.product',
            'creator'
        ])->latest()->paginate(10);

        $boms = BillOfMaterial::where('is_active', true)->with('product')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('pages.manufacturing.work-orders', compact('workOrders', 'boms', 'warehouses'));
    }

    public function storeWorkOrder(Request $request)
    {
        $request->validate([
            'bom_id' => 'required|exists:bills_of_material,id',
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'target_warehouse_id' => 'required|exists:warehouses,id',
            'target_quantity' => 'required|numeric|min:0.0001',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $bom = BillOfMaterial::with('items.product')->findOrFail($request->bom_id);
        $orderNumber = 'WO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $workOrder = ProductionOrder::create([
            'order_number' => $orderNumber,
            'product_id' => $bom->product_id,
            'bom_id' => $bom->id,
            'source_warehouse_id' => $request->source_warehouse_id,
            'target_warehouse_id' => $request->target_warehouse_id,
            'target_quantity' => $request->target_quantity,
            'produced_quantity' => 0,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'status' => 'planned',
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        // Pre-populate planned material consumption
        $ratio = (float) $request->target_quantity / (float) $bom->quantity;
        foreach ($bom->items as $item) {
            $plannedQty = (float) $item->quantity * $ratio;
            ProductionMaterialConsumption::create([
                'production_order_id' => $workOrder->id,
                'product_id' => $item->product_id,
                'warehouse_id' => $request->source_warehouse_id,
                'planned_quantity' => $plannedQty,
                'actual_consumed_quantity' => 0,
                'unit_cost' => $item->unit_cost,
                'total_cost' => 0,
            ]);
        }

        AuditLog::log('created', $workOrder, null, $workOrder->toArray());

        return redirect()->back()->with('success', "Work Order {$orderNumber} planned successfully.");
    }

    public function startWorkOrder($id)
    {
        $workOrder = ProductionOrder::with('materialConsumptions.product')->findOrFail($id);

        if ($workOrder->status !== 'planned') {
            return redirect()->back()->with('error', 'Only planned orders can be started.');
        }

        return DB::transaction(function () use ($workOrder) {
            // Verify stock availability and issue raw materials
            foreach ($workOrder->materialConsumptions as $consumption) {
                $stockLevel = StockLevel::where('product_id', $consumption->product_id)
                    ->where('warehouse_id', $workOrder->source_warehouse_id)
                    ->first();

                $neededQty = (float) $consumption->planned_quantity;

                if (!$stockLevel || $stockLevel->quantity_available < $neededQty) {
                    $prodName = $consumption->product?->name ?? 'Raw Material';
                    return redirect()->back()->with('error', "Insufficient stock for {$prodName} in source warehouse.");
                }

                // Decrement stock
                $stockLevel->quantity_on_hand -= $neededQty;
                $stockLevel->quantity_available -= $neededQty;
                $stockLevel->save();

                // Update actual consumption
                $consumption->update([
                    'actual_consumed_quantity' => $neededQty,
                    'total_cost' => $neededQty * (float) $consumption->unit_cost,
                ]);

                // Write immutable stock movement
                StockMovement::create([
                    'product_id' => $consumption->product_id,
                    'warehouse_id' => $workOrder->source_warehouse_id,
                    'movement_type' => 'production_issue',
                    'reference_type' => 'production_order',
                    'reference_id' => $workOrder->id,
                    'quantity' => -$neededQty,
                    'unit_cost' => $consumption->unit_cost,
                    'total_cost' => $neededQty * (float) $consumption->unit_cost,
                    'notes' => "Material issued for Work Order {$workOrder->order_number}",
                    'created_by' => Auth::id(),
                ]);
            }

            $workOrder->update(['status' => 'in_progress']);
            AuditLog::log('started', $workOrder, ['status' => 'planned'], ['status' => 'in_progress']);

            return redirect()->back()->with('success', "Work Order {$workOrder->order_number} is now In Progress. Materials issued from warehouse.");
        });
    }

    public function completeWorkOrder(Request $request, $id)
    {
        $workOrder = ProductionOrder::with('product')->findOrFail($id);

        if ($workOrder->status !== 'in_progress' && $workOrder->status !== 'qc_pending') {
            return redirect()->back()->with('error', 'Work order must be In Progress to complete.');
        }

        $producedQty = (float) ($request->produced_quantity ?? $workOrder->target_quantity);

        return DB::transaction(function () use ($workOrder, $producedQty) {
            // Increment Finished Good stock in target warehouse
            $targetStock = StockLevel::firstOrCreate([
                'tenant_id' => TenantContext::getTenantId(),
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

            // Write stock movement for finished good production receipt
            StockMovement::create([
                'product_id' => $workOrder->product_id,
                'warehouse_id' => $workOrder->target_warehouse_id,
                'movement_type' => 'production_receipt',
                'reference_type' => 'production_order',
                'reference_id' => $workOrder->id,
                'quantity' => $producedQty,
                'unit_cost' => $workOrder->product?->purchase_price ?? 0,
                'total_cost' => $producedQty * (float) ($workOrder->product?->purchase_price ?? 0),
                'batch_number' => 'MFG-' . date('ymd') . '-' . rand(100, 999),
                'notes' => "Finished goods received from Work Order {$workOrder->order_number}",
                'created_by' => Auth::id(),
            ]);

            $workOrder->update([
                'status' => 'completed',
                'produced_quantity' => $producedQty,
            ]);

            AuditLog::log('completed', $workOrder, ['status' => 'in_progress'], ['status' => 'completed']);

            return redirect()->back()->with('success', "Work Order {$workOrder->order_number} completed. {$producedQty} finished goods added to target warehouse.");
        });
    }

    // --- MATERIAL CONSUMPTION ---
    public function materialConsumption()
    {
        $consumptions = ProductionMaterialConsumption::with(['productionOrder.product', 'product', 'warehouse'])
            ->latest()
            ->paginate(15);

        return view('pages.manufacturing.material-consumption', compact('consumptions'));
    }

    // --- QUALITY CONTROL (QC) ---
    public function qualityControl(Request $request)
    {
        $inspections = QualityInspection::with('inspector')->latest()->paginate(10);
        $workOrders = ProductionOrder::whereIn('status', ['in_progress', 'completed'])->get();

        return view('pages.manufacturing.quality-control', compact('inspections', 'workOrders'));
    }

    public function storeQualityInspection(Request $request)
    {
        $request->validate([
            'inspectable_id' => 'required',
            'sample_size' => 'required|numeric|min:1',
            'passed_quantity' => 'required|numeric|min:0',
            'rejected_quantity' => 'required|numeric|min:0',
            'inspection_date' => 'required|date',
            'status' => 'required|in:pass,fail,rework',
            'remarks' => 'nullable|string',
        ]);

        $inspNumber = 'QC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $qc = QualityInspection::create([
            'inspection_number' => $inspNumber,
            'inspectable_type' => ProductionOrder::class,
            'inspectable_id' => $request->inspectable_id,
            'inspector_id' => Auth::id(),
            'sample_size' => $request->sample_size,
            'passed_quantity' => $request->passed_quantity,
            'rejected_quantity' => $request->rejected_quantity,
            'inspection_date' => $request->inspection_date,
            'status' => $request->status,
            'remarks' => $request->remarks,
            'checkpoints' => [
                ['parameter' => 'Tolerance Check', 'result' => $request->status === 'pass' ? 'Pass' : 'Defect'],
                ['parameter' => 'Visual Finish', 'result' => 'Pass'],
            ],
        ]);

        AuditLog::log('created', $qc, null, $qc->toArray());

        return redirect()->back()->with('success', "Quality inspection {$inspNumber} recorded successfully.");
    }

    // --- WORK CENTERS ---
    public function workCenters()
    {
        $workCenters = WorkCenter::latest()->get();
        return view('pages.manufacturing.work-centers', compact('workCenters'));
    }

    public function storeWorkCenter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
            'hourly_rate' => 'required|numeric|min:0',
            'capacity_per_day_hours' => 'required|numeric|min:1|max:24',
        ]);

        $wc = WorkCenter::create(array_merge($validated, ['is_active' => true]));
        AuditLog::log('created', $wc, null, $wc->toArray());

        return redirect()->back()->with('success', "Work center {$wc->name} created successfully.");
    }
}
