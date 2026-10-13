<?php

namespace Modules\Inventory\Domain\Contracts;

use Modules\Inventory\Domain\Models\StockLevel;
use Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockRepositoryInterface
{
    public function getStockLevel(int $productId, int $warehouseId): ?StockLevel;
    public function getOrCreateStockLevel(int $productId, int $warehouseId): StockLevel;
    public function paginateStockLevels(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function paginateStockMovements(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function recordMovement(array $data): StockMovement;
}
