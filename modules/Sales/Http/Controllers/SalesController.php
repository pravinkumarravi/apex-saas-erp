<?php

namespace Modules\Sales\Http\Controllers;

use Modules\Governance\Domain\Models\AuditLog;
use Modules\Sales\Domain\Models\Customer;
use Modules\Sales\Domain\Models\DeliveryChallan;
use Modules\Sales\Domain\Models\Lead;
use Modules\Inventory\Domain\Models\Product;
use Modules\Sales\Domain\Models\SalesOrder;
use Modules\Sales\Domain\Models\SalesOrderItem;
use Modules\Inventory\Domain\Models\StockLevel;
use Modules\Inventory\Domain\Models\StockMovement;
use Modules\Accounting\Domain\Models\TaxInvoice;
use Modules\Accounting\Domain\Models\TaxInvoiceItem;
use Modules\Identity\Domain\Models\User;
use Modules\Inventory\Domain\Models\Warehouse;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesController
{
    // --- CUSTOMERS ---
    public function customers(Request $request)
    {
        $query = Customer::withCount('salesOrders');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        $customers = $query->latest()->paginate(10);
        return view('pages.crm.customers', compact('customers'));
    }

    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'code' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'state_code' => 'required|string|size:2',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create(array_merge($validated, ['status' => 'active']));
        AuditLog::log('created', $customer, null, $customer->toArray());

        return redirect()->back()->with('success', "Customer {$customer->name} added successfully.");
    }

    // --- LEADS ---
    public function leads(Request $request)
    {
        $query = Lead::with('assignee');

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        $leads = $query->latest()->paginate(12);
        $users = User::all();

        return view('pages.crm.leads', compact('leads', 'users'));
    }

    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'stage' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'expected_value' => 'required|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::create($validated);
        AuditLog::log('created', $lead, null, $lead->toArray());

        return redirect()->back()->with('success', "Lead {$lead->title} recorded.");
    }

    // --- SALES ORDERS ---
    public function salesOrders(Request $request)
    {
        $orders = SalesOrder::with(['customer', 'warehouse', 'items.product', 'creator'])->latest()->paginate(10);
        $customers = Customer::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->whereIn('product_type', ['finished_good', 'service'])->get();

        return view('pages.sales.orders', compact('orders', 'customers', 'warehouses', 'products'));
    }

    public function storeSalesOrder(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $customer = Customer::findOrFail($request->customer_id);
            $tenantState = TenantContext::getStateCode();
            $isInterstate = ($customer->state_code !== $tenantState);

            $soNumber = 'SO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

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
                    'shipped_quantity' => 0,
                ];
            }

            $totalAmount = $subtotal + $taxAmount;

            $so = SalesOrder::create([
                'so_number' => $soNumber,
                'customer_id' => $customer->id,
                'warehouse_id' => $request->warehouse_id,
                'order_date' => $request->order_date,
                'delivery_date' => $request->delivery_date,
                'status' => 'confirmed',
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
                $so->items()->create($itemData);
            }

            AuditLog::log('created', $so, null, $so->toArray());

            return redirect()->back()->with('success', "Sales Order {$soNumber} confirmed successfully.");
        });
    }

    // --- DELIVERY CHALLANS & DISPATCH ---
    public function deliveryChallans(Request $request)
    {
        $challans = DeliveryChallan::with(['salesOrder.customer', 'warehouse', 'dispatcher'])->latest()->paginate(10);
        $confirmedOrders = SalesOrder::whereIn('status', ['confirmed', 'picking'])->with(['customer', 'items.product'])->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('pages.sales.delivery-challans', compact('challans', 'confirmedOrders', 'warehouses'));
    }

    public function storeDeliveryChallan(Request $request)
    {
        $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'vehicle_number' => 'nullable|string|max:30',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:30',
            'eway_bill_number' => 'nullable|string|max:50',
            'dispatch_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $order = SalesOrder::with('items.product')->findOrFail($request->sales_order_id);
            $challanNumber = 'DC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Verify and decrement finished goods stock from warehouse
            foreach ($order->items as $item) {
                $stockLevel = StockLevel::where('product_id', $item->product_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();

                $neededQty = (float) $item->quantity;

                if (!$stockLevel || $stockLevel->quantity_available < $neededQty) {
                    $prodName = $item->product?->name ?? 'Product';
                    return redirect()->back()->with('error', "Insufficient stock for {$prodName} to dispatch.");
                }

                $stockLevel->quantity_on_hand -= $neededQty;
                $stockLevel->quantity_available -= $neededQty;
                $stockLevel->save();

                // Record stock movement ledger
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $request->warehouse_id,
                    'movement_type' => 'sales_dispatch',
                    'reference_type' => 'sales_order',
                    'reference_id' => $order->id,
                    'quantity' => -$neededQty,
                    'unit_cost' => $item->unit_price,
                    'total_cost' => $neededQty * (float) $item->unit_price,
                    'notes' => "Dispatched under Delivery Challan {$challanNumber}",
                    'created_by' => Auth::id(),
                ]);

                $item->update(['shipped_quantity' => $neededQty]);
            }

            $challan = DeliveryChallan::create([
                'challan_number' => $challanNumber,
                'sales_order_id' => $order->id,
                'warehouse_id' => $request->warehouse_id,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'eway_bill_number' => $request->eway_bill_number,
                'dispatch_date' => $request->dispatch_date,
                'status' => 'dispatched',
                'notes' => $request->notes,
                'dispatched_by' => Auth::id(),
            ]);

            $order->update(['status' => 'shipped']);

            // Auto-generate Tax Invoice if not already created
            if (!$order->invoices()->exists()) {
                $tenantState = TenantContext::getStateCode();
                $isInterstate = ($order->customer->state_code !== $tenantState);
                $invNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $invoice = TaxInvoice::create([
                    'invoice_number' => $invNumber,
                    'invoice_type' => 'sales',
                    'party_type' => 'customer',
                    'party_id' => $order->customer_id,
                    'sales_order_id' => $order->id,
                    'invoice_date' => $request->dispatch_date,
                    'due_date' => now()->addDays(30)->toDateString(),
                    'state_of_supply' => $order->customer->state_code,
                    'is_interstate' => $isInterstate,
                    'subtotal' => $order->subtotal,
                    'cgst_amount' => $order->cgst_amount,
                    'sgst_amount' => $order->sgst_amount,
                    'igst_amount' => $order->igst_amount,
                    'tax_amount' => $order->tax_amount,
                    'total_amount' => $order->total_amount,
                    'paid_amount' => 0,
                    'balance_due' => $order->total_amount,
                    'payment_status' => 'unpaid',
                    'gst_breakup' => [
                        'cgst' => $order->cgst_amount,
                        'sgst' => $order->sgst_amount,
                        'igst' => $order->igst_amount,
                    ],
                    'notes' => "Generated automatically upon dispatch under Delivery Challan {$challanNumber}",
                    'created_by' => Auth::id(),
                ]);

                foreach ($order->items as $item) {
                    $invoice->items()->create([
                        'product_id' => $item->product_id,
                        'item_name' => $item->product?->name ?? 'Item',
                        'hsn_code' => $item->hsn_code,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percent' => $item->discount_percent,
                        'gst_rate' => $item->gst_rate,
                        'taxable_value' => $item->quantity * $item->unit_price,
                        'cgst_amount' => $isInterstate ? 0 : round($item->tax_amount / 2, 2),
                        'sgst_amount' => $isInterstate ? 0 : ($item->tax_amount - round($item->tax_amount / 2, 2)),
                        'igst_amount' => $isInterstate ? $item->tax_amount : 0,
                        'total_amount' => $item->total_amount,
                    ]);
                }
            }

            AuditLog::log('dispatched', $challan, null, $challan->toArray());

            return redirect()->back()->with('success', "Delivery Challan {$challanNumber} created and goods dispatched successfully. Tax invoice generated.");
        });
    }
}
