<?php

namespace Modules\Inventory\Application\Contracts;

use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Domain\Models\StockLevel;

interface InventoryServiceInterface
{
    public function adjustStock(StockAdjustmentDTO $dto): StockLevel;
    public function hasAvailableStock(int $productId, int $warehouseId, float $requiredQuantity): bool;
}
