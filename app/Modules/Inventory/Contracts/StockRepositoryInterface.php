<?php

namespace Modules\Inventory\Contracts;

use Modules\Inventory\Models\StockLevel;
use Modules\Inventory\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockRepositoryInterface
{
    public function getStockLevel(int $productId, int $warehouseId): ?StockLevel;
    public function getOrCreateStockLevel(int $productId, int $warehouseId): StockLevel;
    public function paginateStockLevels(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function paginateStockMovements(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function recordMovement(array $data): StockMovement;
}
