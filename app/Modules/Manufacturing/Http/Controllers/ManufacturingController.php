<?php

namespace Modules\Manufacturing\Http\Controllers;

use Modules\Manufacturing\Contracts\BomRepositoryInterface;
use Modules\Manufacturing\Contracts\ProductionOrderRepositoryInterface;
use Modules\Manufacturing\Contracts\WorkCenterRepositoryInterface;
use Modules\Manufacturing\Models\BillOfMaterial;
use Modules\Manufacturing\Models\ProductionMaterialConsumption;
use Modules\Manufacturing\Models\ProductionOrder;
use Modules\Manufacturing\Models\QualityInspection;
use Modules\Manufacturing\Models\WorkCenter;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManufacturingController
{
    public function __construct(
        protected BomRepositoryInterface $bomRepo,
        protected ProductionOrderRepositoryInterface $orderRepo,
        protected WorkCenterRepositoryInterface $workCenterRepo,
    ) {}

    // --- BILLS OF MATERIAL (BOM) ---
    public function bom(Request $request)
    {
        $boms = $this->bomRepo->paginate(10);
        $finishedGoods = apply_filters('manufacturing_finished_goods_options', collect());
        $rawMaterials = apply_filters('manufacturing_raw_materials_options', collect());
        $products = $finishedGoods;

        return view('pages.manufacturing.bom', compact('boms', 'finishedGoods', 'rawMaterials', 'products'));
    }

    public function storeBom(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'bom_number' => 'required|string|max:50',
            'version' => 'required|string|max:10',
            'quantity' => 'required|numeric|min:0.0001',
            'routing_instructions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.scrap_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $estimatedCost = 0;
        $itemsData = [];

        foreach ($request->items as $itemInput) {
            $component = apply_filters('inventory_find_product', null, $itemInput['product_id']);
            $qty = (float) $itemInput['quantity'];
            $scrap = (float) ($itemInput['scrap_percentage'] ?? 0);
            $effectiveQty = $qty * (1 + ($scrap / 100));
            $cost = (float) ($component?->purchase_price ?? 0);
            $lineCost = $effectiveQty * $cost;

            $estimatedCost += $lineCost;

            $itemsData[] = [
                'product_id' => $itemInput['product_id'],
                'quantity' => $qty,
                'scrap_percentage' => $scrap,
                'unit_cost' => $cost,
            ];
        }

        $bom = $this->bomRepo->create([
            'product_id' => $request->product_id,
            'bom_number' => $request->bom_number,
            'version' => $request->version,
            'quantity' => $request->quantity,
            'estimated_cost' => $estimatedCost,
            'routing_instructions' => $request->routing_instructions,
            'is_active' => true,
        ], $itemsData);

        do_action('audit_log', 'created', $bom, null, $bom->toArray());

        return redirect()->back()->with('success', "Bill of Materials {$bom->bom_number} created successfully.");
    }

    // --- PRODUCTION ORDERS (WORK ORDERS) ---
    public function workOrders(Request $request)
    {
        $workOrders = $this->orderRepo->paginate(10);
        $boms = BillOfMaterial::where('is_active', true)->with('product')->get();
        $warehouses = apply_filters('manufacturing_warehouse_options', collect());

        return view('pages.manufacturing.work-orders', compact('workOrders', 'boms', 'warehouses'));
    }

    public function storeWorkOrder(Request $request)
    {
        $request->validate([
            'bom_id' => 'required|exists:bills_of_material,id',
            'source_warehouse_id' => 'required',
            'target_warehouse_id' => 'required',
            'target_quantity' => 'required|numeric|min:0.0001',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $bom = $this->bomRepo->findOrFail($request->bom_id);
        $orderNumber = 'WO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // Pre-populate planned material consumption
        $ratio = (float) $request->target_quantity / (float) $bom->quantity;
        $consumptions = [];
        foreach ($bom->items as $item) {
            $plannedQty = (float) $item->quantity * $ratio;
            $consumptions[] = [
                'product_id' => $item->product_id,
                'warehouse_id' => $request->source_warehouse_id,
                'planned_quantity' => $plannedQty,
                'actual_consumed_quantity' => 0,
                'unit_cost' => $item->unit_cost,
                'total_cost' => 0,
            ];
        }

        $workOrder = $this->orderRepo->create([
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
        ], $consumptions);

        do_action('audit_log', 'created', $workOrder, null, $workOrder->toArray());

        return redirect()->back()->with('success', "Work Order {$orderNumber} planned successfully.");
    }

    public function startWorkOrder($id)
    {
        $workOrder = $this->orderRepo->findOrFail($id);

        if ($workOrder->status !== 'planned') {
            return redirect()->back()->with('error', 'Only planned orders can be started.');
        }

        // Validate stock availability in Inventory module via filter hook
        $stockCheck = apply_filters('manufacturing_validate_materials', true, $workOrder);
        if ($stockCheck !== true) {
            return redirect()->back()->with('error', is_string($stockCheck) ? $stockCheck : 'Insufficient stock in source warehouse.');
        }

        return DB::transaction(function () use ($workOrder) {
            // Update actual consumption in Manufacturing
            foreach ($workOrder->materialConsumptions as $consumption) {
                $neededQty = (float) $consumption->planned_quantity;
                $consumption->update([
                    'actual_consumed_quantity' => $neededQty,
                    'total_cost' => $neededQty * (float) $consumption->unit_cost,
                ]);
            }

            $workOrder = $this->orderRepo->updateStatus($workOrder->id, 'in_progress');

            // Dispatch action for Inventory module to issue materials and record stock movement
            do_action('production_order_materials_issued', $workOrder);
            do_action('audit_log', 'started', $workOrder, ['status' => 'planned'], ['status' => 'in_progress']);

            return redirect()->back()->with('success', "Work Order {$workOrder->order_number} is now In Progress. Materials issued from warehouse.");
        });
    }

    public function completeWorkOrder(Request $request, $id)
    {
        $workOrder = $this->orderRepo->findOrFail($id);

        if ($workOrder->status !== 'in_progress' && $workOrder->status !== 'qc_pending') {
            return redirect()->back()->with('error', 'Work order must be In Progress to complete.');
        }

        $producedQty = (float) ($request->produced_quantity ?? $workOrder->target_quantity);

        return DB::transaction(function () use ($workOrder, $producedQty) {
            $workOrder = $this->orderRepo->updateStatus($workOrder->id, 'completed', $producedQty);

            // Dispatch action for Inventory module to receive finished goods and record stock movement
            do_action('production_order_completed', $workOrder, $producedQty);
            do_action('audit_log', 'completed', $workOrder, ['status' => 'in_progress'], ['status' => 'completed']);

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

        do_action('audit_log', 'created', $qc, null, $qc->toArray());

        return redirect()->back()->with('success', "Quality inspection {$inspNumber} recorded successfully.");
    }

    // --- WORK CENTERS ---
    public function workCenters()
    {
        $workCenters = $this->workCenterRepo->allActive();
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

        $wc = $this->workCenterRepo->create(array_merge($validated, ['is_active' => true]));
        do_action('audit_log', 'created', $wc, null, $wc->toArray());

        return redirect()->back()->with('success', "Work center {$wc->name} created successfully.");
    }
}
