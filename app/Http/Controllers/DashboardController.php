<?php

namespace App\Http\Controllers;

use Modules\Governance\Models\ApprovalRequest;
use Modules\Sales\Models\Customer;
use Modules\HumanResources\Models\Employee;
use Modules\Inventory\Models\Product;
use Modules\Manufacturing\Models\ProductionOrder;
use Modules\Procurement\Models\PurchaseOrder;
use Modules\Sales\Models\SalesOrder;
use Modules\Inventory\Models\StockLevel;
use Modules\Procurement\Models\Supplier;
use Modules\Accounting\Models\TaxInvoice;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class DashboardController
{
    public function index(Request $request)
    {
        $tenant = TenantContext::getTenant();

        // 1. Financial KPIs
        $totalRevenue = TaxInvoice::where('invoice_type', 'sales')->sum('total_amount');
        $totalReceivables = TaxInvoice::where('invoice_type', 'sales')->sum('balance_due');
        $totalPaid = TaxInvoice::where('invoice_type', 'sales')->sum('paid_amount');
        
        // Output GST vs Input GST
        $outputGst = TaxInvoice::where('invoice_type', 'sales')->sum('tax_amount');
        $inputGst = PurchaseOrder::whereIn('status', ['received', 'ordered'])->sum('tax_amount');
        $netGstPayable = max(0, $outputGst - $inputGst);

        // 2. Operational KPIs
        $activeSalesOrders = SalesOrder::whereNotIn('status', ['delivered', 'cancelled'])->count();
        $activePurchaseOrders = PurchaseOrder::whereNotIn('status', ['received', 'cancelled'])->count();
        $activeWorkOrders = ProductionOrder::whereNotIn('status', ['completed', 'cancelled'])->count();
        $totalEmployees = Employee::where('status', 'active')->count();

        // 3. Inventory & Valuation
        $totalProducts = Product::where('is_active', true)->count();
        $inventoryValuation = StockLevel::with('product')->get()->sum(function ($level) {
            return $level->quantity_on_hand * ($level->product?->purchase_price ?? 0);
        });

        // 4. Low stock alerts (items below reorder level)
        $lowStockItems = Product::with('stockLevels')
            ->where('is_active', true)
            ->get()
            ->filter(function ($product) {
                return $product->total_stock_on_hand <= $product->reorder_level;
            })
            ->take(5);

        // 5. Recent Transactions
        $recentInvoices = TaxInvoice::with('customer')
            ->where('invoice_type', 'sales')
            ->latest()
            ->take(5)
            ->get();

        $recentWorkOrders = ProductionOrder::with('product')
            ->latest()
            ->take(5)
            ->get();

        $pendingApprovals = ApprovalRequest::with(['requester'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('pages.dashboard.executive', compact(
            'tenant',
            'totalRevenue',
            'totalReceivables',
            'totalPaid',
            'outputGst',
            'inputGst',
            'netGstPayable',
            'activeSalesOrders',
            'activePurchaseOrders',
            'activeWorkOrders',
            'totalEmployees',
            'totalProducts',
            'inventoryValuation',
            'lowStockItems',
            'recentInvoices',
            'recentWorkOrders',
            'pendingApprovals'
        ));
    }

    public function analytics()
    {
        $tenant = TenantContext::getTenant();

        // Sales by product
        $products = Product::where('product_type', 'finished_good')->get();
        $topCustomers = Customer::withCount('salesOrders')->take(5)->get();
        $suppliers = Supplier::withCount('purchaseOrders')->take(5)->get();

        return view('pages.dashboard.analytics', compact(
            'tenant',
            'products',
            'topCustomers',
            'suppliers'
        ));
    }
}
