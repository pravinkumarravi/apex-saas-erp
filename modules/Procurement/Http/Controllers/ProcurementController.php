<?php

namespace Modules\Procurement\Http\Controllers;

use Modules\Governance\Domain\Models\ApprovalRequest;
use Modules\Governance\Domain\Models\ApprovalWorkflow;
use Modules\Governance\Domain\Models\AuditLog;
use Modules\Procurement\Domain\Models\GoodsReceiptItem;
use Modules\Procurement\Domain\Models\GoodsReceiptNote;
use Modules\Inventory\Domain\Models\Product;
use Modules\Procurement\Domain\Models\PurchaseOrder;
use Modules\Procurement\Domain\Models\PurchaseOrderItem;
use Modules\Inventory\Domain\Models\StockLevel;
use Modules\Inventory\Domain\Models\StockMovement;
use Modules\Procurement\Domain\Models\Supplier;
use Modules\Inventory\Domain\Models\Warehouse;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementController
{
    // --- SUPPLIERS ---
    public function suppliers(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        $suppliers = $query->latest()->paginate(10);
        return view('pages.procurement.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'state_code' => 'required|string|size:2',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
        ]);

        $bankDetails = [
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
        ];

        $supplier = Supplier::create(array_merge($validated, [
            'bank_details' => $bankDetails,
            'status' => 'active',
        ]));

        AuditLog::log('created', $supplier, null, $supplier->toArray());

        return redirect()->back()->with('success', 'Supplier created successfully.');
    }

    // --- PURCHASE ORDERS ---
    public function purchaseOrders(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse', 'items.product', 'creator'])
            ->latest()
            ->paginate(10);

        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('pages.procurement.purchase-orders', compact('purchaseOrders', 'suppliers', 'warehouses', 'products'));
    }

    public function storePurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $supplier = Supplier::findOrFail($request->supplier_id);
            $tenantState = TenantContext::getStateCode();
            $isInterstate = ($supplier->state_code !== $tenantState);

            $poNumber = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $subtotal = 0;
            $taxAmount = 0;
            $cgstAmount = 0;
            $sgstAmount = 0;
            $igstAmount = 0;

            $itemsData = [];

            foreach ($request->items as $itemInput) {
                $product = Product::findOrFail($itemInput['product_id']);
                $qty = (float) $itemInput['quantity'];
                $price = (float) $itemInput['unit_price'];
                $lineSubtotal = $qty * $price;

                $gstRate = (float) ($product->gst_rate ?? 18.0);
                $lineTax = round(($lineSubtotal * $gstRate) / 100, 2);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;

                if ($isInterstate) {
                    $igstAmount += $lineTax;
                } else {
                    $halfTax = round($lineTax / 2, 2);
                    $cgstAmount += $halfTax;
                    $sgstAmount += $lineTax - $halfTax;
                }

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_percent' => 0,
                    'hsn_code' => $product->hsn_code,
                    'gst_rate' => $gstRate,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineTotal,
                    'received_quantity' => 0,
                ];
            }

            $totalAmount = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $request->warehouse_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'igst_amount' => $igstAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $itemData) {
                $po->items()->create($itemData);
            }

            // Check if approval is required (> 50,000 threshold)
            if ($totalAmount >= 50000) {
                $po->update(['status' => 'pending_approval']);
                ApprovalRequest::create([
                    'approvable_type' => PurchaseOrder::class,
                    'approvable_id' => $po->id,
                    'current_step' => 1,
                    'total_steps' => 2,
                    'status' => 'pending',
                    'requested_by' => Auth::id(),
                ]);
            } else {
                $po->update(['status' => 'approved', 'approved_by' => Auth::id()]);
            }

            AuditLog::log('created', $po, null, $po->toArray());

            return redirect()->back()->with('success', "Purchase Order {$poNumber} created successfully.");
        });
    }

    public function approvePurchaseOrder($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        $approvalReq = ApprovalRequest::where('approvable_type', PurchaseOrder::class)
            ->where('approvable_id', $po->id)
            ->first();

        if ($approvalReq) {
            $approvalReq->update([
                'status' => 'approved',
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
            ]);
        }

        AuditLog::log('approved', $po, ['status' => 'pending_approval'], ['status' => 'approved']);

        return redirect()->back()->with('success', "Purchase Order {$po->po_number} has been approved.");
    }

    // --- GOODS RECEIPT NOTES (GRN) ---
    public function goodsReceipts(Request $request)
    {
        $goodsReceipts = GoodsReceiptNote::with(['purchaseOrder.supplier', 'warehouse', 'items.product', 'receiver'])
            ->latest()
            ->paginate(10);

        $eligiblePOs = PurchaseOrder::whereIn('status', ['approved', 'ordered', 'partially_received'])
            ->with(['items.product', 'supplier'])
            ->get();

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('pages.procurement.goods-receipts', compact('goodsReceipts', 'eligiblePOs', 'warehouses'));
    }

    public function storeGoodsReceipt(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'delivery_challan_no' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0.0001',
            'items.*.accepted_quantity' => 'required|numeric|min:0',
            'items.*.rejected_quantity' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $po = PurchaseOrder::with('items')->findOrFail($request->purchase_order_id);
            $grnNumber = 'GRN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $grn = GoodsReceiptNote::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $po->id,
                'warehouse_id' => $request->warehouse_id,
                'receipt_date' => $request->receipt_date,
                'delivery_challan_no' => $request->delivery_challan_no,
                'remarks' => $request->remarks,
                'received_by' => Auth::id(),
            ]);

            $allCompleted = true;

            foreach ($request->items as $itemInput) {
                $poItem = PurchaseOrderItem::findOrFail($itemInput['po_item_id']);
                $receivedQty = (float) $itemInput['received_quantity'];
                $acceptedQty = (float) $itemInput['accepted_quantity'];
                $rejectedQty = (float) ($itemInput['rejected_quantity'] ?? 0);
                $batchNumber = $itemInput['batch_number'] ?? ('LOT-' . date('ymd') . '-' . rand(100, 999));

                GoodsReceiptItem::create([
                    'goods_receipt_note_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'received_quantity' => $receivedQty,
                    'accepted_quantity' => $acceptedQty,
                    'rejected_quantity' => $rejectedQty,
                    'batch_number' => $batchNumber,
                    'rejection_reason' => $itemInput['rejection_reason'] ?? null,
                ]);

                // Update PO Item received quantity
                $newReceivedTotal = (float) $poItem->received_quantity + $acceptedQty;
                $poItem->update(['received_quantity' => $newReceivedTotal]);

                if ($newReceivedTotal < (float) $poItem->quantity) {
                    $allCompleted = false;
                }

                // Increment Warehouse Stock Level
                if ($acceptedQty > 0) {
                    $stockLevel = StockLevel::firstOrCreate([
                        'tenant_id' => TenantContext::getTenantId(),
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => $request->warehouse_id,
                    ], [
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'quantity_available' => 0,
                    ]);

                    $stockLevel->quantity_on_hand += $acceptedQty;
                    $stockLevel->quantity_available += $acceptedQty;
                    $stockLevel->save();

                    // Record Immutable Stock Movement Ledger
                    StockMovement::create([
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => $request->warehouse_id,
                        'movement_type' => 'purchase_receipt',
                        'reference_type' => 'goods_receipt_note',
                        'reference_id' => $grn->id,
                        'quantity' => $acceptedQty,
                        'unit_cost' => $poItem->unit_price,
                        'total_cost' => $acceptedQty * (float) $poItem->unit_price,
                        'batch_number' => $batchNumber,
                        'notes' => "Inward receipt via GRN {$grnNumber} for PO {$po->po_number}",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Update PO Status
            $po->update(['status' => $allCompleted ? 'received' : 'partially_received']);

            AuditLog::log('created', $grn, null, $grn->toArray());

            return redirect()->back()->with('success', "Goods Receipt Note {$grnNumber} processed. Inventory balances updated successfully.");
        });
    }
}
