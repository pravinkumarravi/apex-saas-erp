<?php

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Inventory\Domain\Contracts\StockRepositoryInterface;
use Modules\Inventory\Domain\Models\StockLevel;
use Modules\Inventory\Domain\Models\StockMovement;
use App\Services\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentStockRepository implements StockRepositoryInterface
{
    public function getStockLevel(int $productId, int $warehouseId): ?StockLevel
    {
        return StockLevel::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    public function getOrCreateStockLevel(int $productId, int $warehouseId): StockLevel
    {
        return StockLevel::firstOrCreate([
            'tenant_id' => TenantContext::getTenantId(),
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ], [
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
            'quantity_available' => 0,
        ]);
    }

    public function paginateStockLevels(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = StockLevel::with(['product.category', 'product.uom', 'warehouse']);

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->whereHas('product', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function paginateStockMovements(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = StockMovement::with(['product', 'warehouse', 'creator']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function recordMovement(array $data): StockMovement
    {
        return StockMovement::create($data);
    }
}
