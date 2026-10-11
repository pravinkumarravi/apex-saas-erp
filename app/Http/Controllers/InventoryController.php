<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\UnitOfMeasure;
use App\Models\Warehouse;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController
{
    // --- PRODUCTS ---
    public function products(Request $request)
    {
        $query = Product::with(['category', 'uom', 'stockLevels.warehouse']);

        if ($request->filled('type')) {
            $query->where('product_type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
                    ->orWhere('hsn_code', 'like', "%{$s}%");
            });
        }

        $products = $query->latest()->paginate(10);
        $categories = ProductCategory::all();
        $uoms = UnitOfMeasure::all();

        return view('pages.inventory.products', compact('products', 'categories', 'uoms'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:product_categories,id',
            'uom_id' => 'nullable|exists:units_of_measure,id',
            'hsn_code' => 'nullable|string|max:10',
            'product_type' => 'required|in:raw_material,work_in_progress,finished_good,consumable,service',
            'purchase_price' => 'required|numeric|min:0',
            'sales_price' => 'required|numeric|min:0',
            'gst_rate' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'safety_stock' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        AuditLog::log('created', $product, null, $product->toArray());

        return redirect()->back()->with('success', "Product {$product->name} ({$product->sku}) added successfully.");
    }

    // --- STOCK LEVELS ---
    public function stockLevels(Request $request)
    {
        $query = StockLevel::with(['product.category', 'product.uom', 'warehouse']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('product', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            });
        }

        $stockLevels = $query->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('pages.inventory.stock-levels', compact('stockLevels', 'warehouses', 'products'));
    }

    // --- STOCK ADJUSTMENT / TRANSFER ---
    public function adjustStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:adjustment_in,adjustment_out,scrap',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::findOrFail($request->product_id);
            $qty = (float) $request->quantity;
            $cost = (float) ($request->unit_cost ?? $product->purchase_price);

            $stockLevel = StockLevel::firstOrCreate([
                'tenant_id' => TenantContext::getTenantId(),
                'product_id' => $product->id,
                'warehouse_id' => $request->warehouse_id,
            ], [
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
            ]);

            if ($request->type === 'adjustment_in') {
                $stockLevel->quantity_on_hand += $qty;
                $stockLevel->quantity_available += $qty;
            } else {
                if ($stockLevel->quantity_available < $qty) {
                    return redirect()->back()->with('error', 'Insufficient available stock for deduction.');
                }
                $stockLevel->quantity_on_hand -= $qty;
                $stockLevel->quantity_available -= $qty;
            }

            $stockLevel->save();

            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $request->warehouse_id,
                'movement_type' => $request->type,
                'reference_type' => 'manual_adjustment',
                'reference_id' => Auth::id(),
                'quantity' => $request->type === 'adjustment_in' ? $qty : -$qty,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            AuditLog::log('adjusted_stock', $stockLevel, null, ['type' => $request->type, 'qty' => $qty]);

            return redirect()->back()->with('success', 'Stock level adjusted successfully.');
        });
    }

    // --- STOCK MOVEMENTS LEDGER ---
    public function stockMovements(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse', 'creator']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        $movements = $query->latest()->paginate(15);
        $products = Product::all();
        $warehouses = Warehouse::all();

        return view('pages.inventory.stock-movements', compact('movements', 'products', 'warehouses'));
    }

    // --- WAREHOUSES ---
    public function warehouses()
    {
        $warehouses = Warehouse::withCount('stockLevels')->latest()->get();
        return view('pages.inventory.warehouses', compact('warehouses'));
    }

    public function storeWarehouse(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:30',
        ]);

        $wh = Warehouse::create(array_merge($validated, ['is_active' => true]));
        AuditLog::log('created', $wh, null, $wh->toArray());

        return redirect()->back()->with('success', "Warehouse {$wh->name} added successfully.");
    }
}
