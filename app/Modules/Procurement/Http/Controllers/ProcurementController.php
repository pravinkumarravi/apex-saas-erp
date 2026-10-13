<?php

namespace Modules\Procurement\Http\Controllers;

use Modules\Procurement\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Procurement\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Procurement\Contracts\SupplierRepositoryInterface;
use Modules\Procurement\Models\GoodsReceiptItem;
use Modules\Procurement\Models\GoodsReceiptNote;
use Modules\Procurement\Models\PurchaseOrder;
use Modules\Procurement\Models\PurchaseOrderItem;
use Modules\Procurement\Models\Supplier;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementController
{
    public function __construct(
        protected SupplierRepositoryInterface $supplierRepo,
        protected PurchaseOrderRepositoryInterface $poRepo,
        protected GoodsReceiptRepositoryInterface $grnRepo,
    ) {}

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

        $supplier = $this->supplierRepo->create(array_merge($validated, [
            'bank_details' => $bankDetails,
            'status' => 'active',
        ]));

        do_action('audit_log', 'created', $supplier, null, $supplier->toArray());

        return redirect()->back()->with('success', 'Supplier created successfully.');
    }

    // --- PURCHASE ORDERS ---
    public function purchaseOrders(Request $request)
    {
        $purchaseOrders = $this->poRepo->paginate(10);
        $suppliers = $this->supplierRepo->allActive();
        $warehouses = apply_filters('procurement_warehouse_options', collect());
        $products = apply_filters('procurement_product_options', collect());

        return view('pages.procurement.purchase-orders', compact('purchaseOrders', 'suppliers', 'warehouses', 'products'));
    }

    public function storePurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $supplier = $this->supplierRepo->findOrFail($request->supplier_id);
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
                $product = apply_filters('inventory_find_product', null, $itemInput['product_id']);
                $qty = (float) $itemInput['quantity'];
                $price = (float) $itemInput['unit_price'];
                $lineSubtotal = $qty * $price;

                $gstRate = (float) ($product?->gst_rate ?? 18.0);
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
                    'product_id' => $itemInput['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_percent' => 0,
                    'hsn_code' => $product?->hsn_code,
                    'gst_rate' => $gstRate,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineTotal,
                    'received_quantity' => 0,
                ];
            }

            $totalAmount = $subtotal + $taxAmount;

            $po = $this->poRepo->create([
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
            ], $itemsData);

            // Check if approval is required (> 50,000 threshold)
            if ($totalAmount >= 50000) {
                $this->poRepo->updateStatus($po->id, 'pending_approval');
                do_action('procurement_order_approval_needed', $po);
            } else {
                $this->poRepo->updateStatus($po->id, 'approved', Auth::id());
            }

            do_action('audit_log', 'created', $po, null, $po->toArray());

            return redirect()->back()->with('success', "Purchase Order {$poNumber} created successfully.");
        });
    }

    public function approvePurchaseOrder($id)
    {
        $po = $this->poRepo->updateStatus($id, 'approved', Auth::id());
        do_action('procurement_order_approved', $po);
        do_action('audit_log', 'approved', $po, ['status' => 'pending_approval'], ['status' => 'approved']);

        return redirect()->back()->with('success', "Purchase Order {$po->po_number} has been approved.");
    }

    // --- GOODS RECEIPT NOTES (GRN) ---
    public function goodsReceipts(Request $request)
    {
        $goodsReceipts = $this->grnRepo->paginate(10);

        $eligiblePOs = PurchaseOrder::whereIn('status', ['approved', 'ordered', 'partially_received'])
            ->with(['items.product', 'supplier'])
            ->get();

        $warehouses = apply_filters('procurement_warehouse_options', collect());

        return view('pages.procurement.goods-receipts', compact('goodsReceipts', 'eligiblePOs', 'warehouses'));
    }

    public function storeGoodsReceipt(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required',
            'receipt_date' => 'required|date',
            'delivery_challan_no' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0.0001',
            'items.*.accepted_quantity' => 'required|numeric|min:0',
            'items.*.rejected_quantity' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $po = $this->poRepo->findOrFail($request->purchase_order_id);
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
            $itemsForStock = [];

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

                if ($acceptedQty > 0) {
                    $itemsForStock[] = [
                        'product_id' => $poItem->product_id,
                        'accepted_quantity' => $acceptedQty,
                        'unit_price' => $poItem->unit_price,
                        'batch_number' => $batchNumber,
                    ];
                }
            }

            // Update PO Status
            $this->poRepo->updateStatus($po->id, $allCompleted ? 'received' : 'partially_received');

            // Dispatch action for Inventory module to handle stock addition and movements
            do_action('procurement_goods_received', $grn, $itemsForStock);
            do_action('audit_log', 'created', $grn, null, $grn->toArray());

            return redirect()->back()->with('success', "Goods Receipt Note {$grnNumber} processed. Inventory balances updated successfully.");
        });
    }
}
