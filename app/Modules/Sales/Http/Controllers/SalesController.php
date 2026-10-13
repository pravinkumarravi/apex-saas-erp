<?php

namespace Modules\Sales\Http\Controllers;

use Modules\Sales\Contracts\CustomerRepositoryInterface;
use Modules\Sales\Contracts\DeliveryChallanRepositoryInterface;
use Modules\Sales\Contracts\LeadRepositoryInterface;
use Modules\Sales\Contracts\SalesOrderRepositoryInterface;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\DeliveryChallan;
use Modules\Sales\Models\Lead;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Models\SalesOrderItem;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesController
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepo,
        protected LeadRepositoryInterface $leadRepo,
        protected SalesOrderRepositoryInterface $soRepo,
        protected DeliveryChallanRepositoryInterface $challanRepo,
    ) {}

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
        $states = [
            '27' => 'Maharashtra',
            '29' => 'Karnataka',
            '33' => 'Tamil Nadu',
            '07' => 'Delhi',
            '24' => 'Gujarat',
            '06' => 'Haryana',
            '09' => 'Uttar Pradesh',
            '19' => 'West Bengal',
            '32' => 'Kerala',
            '36' => 'Telangana',
        ];

        return view('sales::customers', compact('customers', 'states'));
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
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = $this->customerRepo->create(array_merge($validated, ['status' => 'active']));
        do_action('audit_log', 'created', $customer, null, $customer->toArray());
        do_action('after_customer_created', $customer);

        return redirect()->back()->with('success', "Customer {$customer->name} added successfully.");
    }

    // --- LEADS & CRM ---
    public function leads(Request $request)
    {
        $leads = $this->leadRepo->paginate(10, [
            'stage' => $request->stage,
            'search' => $request->search,
        ]);

        $stages = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        $leads = apply_filters('crm_leads_list', $leads);

        return view('sales::leads', compact('leads', 'stages'));
    }

    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'estimated_value' => 'nullable|numeric|min:0',
            'stage' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $lead = $this->leadRepo->create($validated);
        do_action('audit_log', 'created', $lead, null, $lead->toArray());
        do_action('after_lead_created', $lead);

        return redirect()->back()->with('success', "Lead {$lead->title} recorded.");
    }

    public function convertLead($id)
    {
        $lead = $this->leadRepo->findOrFail($id);
        $this->leadRepo->updateStage($lead->id, 'won');
        do_action('audit_log', 'converted', $lead, null, ['stage' => 'won']);
        do_action('after_lead_converted', $lead);

        return redirect()->back()->with('success', "Lead {$lead->title} marked as won.");
    }

    // --- SALES ORDERS ---
    public function salesOrders(Request $request)
    {
        return $this->orders($request);
    }

    public function orders(Request $request)
    {

        $orders = $this->soRepo->paginate(10, [
            'status' => $request->status,
        ]);

        $customers = $this->customerRepo->allActive();
        $warehouses = apply_filters('sales_warehouse_options', collect());
        $products = apply_filters('sales_product_options', collect());

        $orders = apply_filters('sales_orders_list', $orders);

        return view('sales::orders', compact('orders', 'customers', 'warehouses', 'products'));
    }

    public function storeSalesOrder(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $customer = $this->customerRepo->findOrFail($request->customer_id);
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
                    'shipped_quantity' => 0,
                ];
            }

            $totalAmount = $subtotal + $taxAmount;

            $so = $this->soRepo->create([
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
            ], $itemsData);

            do_action('audit_log', 'created', $so, null, $so->toArray());
            do_action('after_sales_order_created', $so);

            return redirect()->back()->with('success', "Sales Order {$soNumber} confirmed successfully.");
        });
    }

    // --- DELIVERY CHALLANS & DISPATCH ---
    public function deliveryChallans(Request $request)
    {
        $challans = $this->challanRepo->paginate(10);
        $orders = SalesOrder::whereIn('status', ['confirmed', 'picking'])->with(['customer', 'items.product'])->get();
        $warehouses = apply_filters('sales_warehouse_options', collect());

        $challans = apply_filters('delivery_challans_list', $challans);

        return view('sales::delivery-challans', compact('challans', 'orders', 'warehouses'));
    }

    public function storeDeliveryChallan(Request $request)
    {
        $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required',
            'vehicle_number' => 'nullable|string|max:30',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:30',
            'eway_bill_number' => 'nullable|string|max:50',
            'dispatch_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $order = $this->soRepo->findOrFail($request->sales_order_id);
            $challanNumber = 'DC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Verify stock availability via filter hook
            $stockCheck = apply_filters('sales_validate_dispatch_stock', true, $order, $request->warehouse_id);
            if ($stockCheck !== true) {
                return redirect()->back()->with('error', is_string($stockCheck) ? $stockCheck : 'Insufficient stock to dispatch.');
            }

            $challan = $this->challanRepo->create([
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

            $this->soRepo->updateStatus($order->id, 'shipped');

            // Dispatch inter-module action: Inventory decrements stock, Accounting generates Tax Invoice
            do_action('sales_delivery_challan_dispatched', $challan, $order);
            do_action('audit_log', 'dispatched', $challan, null, $challan->toArray());
            do_action('after_delivery_challan_created', $challan);

            return redirect()->back()->with('success', "Delivery Challan {$challanNumber} created and goods dispatched successfully. Tax invoice generated.");
        });
    }
}
