<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Governance\Domain\Models\AuditLog;
use Modules\Inventory\Application\Contracts\InventoryServiceInterface;
use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Domain\Contracts\ProductRepositoryInterface;
use Modules\Inventory\Domain\Contracts\StockRepositoryInterface;
use Modules\Inventory\Domain\Contracts\WarehouseRepositoryInterface;
use Modules\Inventory\Domain\Models\ProductCategory;
use Modules\Inventory\Domain\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected WarehouseRepositoryInterface $warehouseRepository,
        protected StockRepositoryInterface $stockRepository,
        protected InventoryServiceInterface $inventoryService,
    ) {}

    // --- PRODUCTS ---
    public function products(Request $request)
    {
        $products = $this->productRepository->paginate(10, [
            'type' => $request->type,
            'search' => $request->search,
        ]);

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

        $product = $this->productRepository->create($validated);

        AuditLog::log('created', $product, null, $product->toArray());

        return redirect()->back()->with('success', "Product {$product->name} ({$product->sku}) added successfully.");
    }

    // --- STOCK LEVELS ---
    public function stockLevels(Request $request)
    {
        $stockLevels = $this->stockRepository->paginateStockLevels(15, [
            'warehouse_id' => $request->warehouse_id,
            'search' => $request->search,
        ]);

        $warehouses = $this->warehouseRepository->allActive();
        $products = $this->productRepository->allActive();

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

        try {
            $dto = new StockAdjustmentDTO(
                productId: (int) $request->product_id,
                warehouseId: (int) $request->warehouse_id,
                type: $request->type,
                quantity: (float) $request->quantity,
                unitCost: $request->filled('unit_cost') ? (float) $request->unit_cost : null,
                referenceType: 'manual_adjustment',
                referenceId: Auth::id(),
                notes: $request->notes,
                userId: Auth::id(),
            );

            $this->inventoryService->adjustStock($dto);

            return redirect()->back()->with('success', 'Stock level adjusted successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- STOCK MOVEMENTS LEDGER ---
    public function stockMovements(Request $request)
    {
        $movements = $this->stockRepository->paginateStockMovements(15, [
            'product_id' => $request->product_id,
            'movement_type' => $request->movement_type,
        ]);

        $products = $this->productRepository->allActive();
        $warehouses = $this->warehouseRepository->allActive();

        return view('pages.inventory.stock-movements', compact('movements', 'products', 'warehouses'));
    }

    // --- WAREHOUSES ---
    public function warehouses()
    {
        $warehouses = $this->warehouseRepository->allActive();
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

        $wh = $this->warehouseRepository->create(array_merge($validated, ['is_active' => true]));
        AuditLog::log('created', $wh, null, $wh->toArray());

        return redirect()->back()->with('success', "Warehouse {$wh->name} added successfully.");
    }
}
