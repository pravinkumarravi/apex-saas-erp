<?php

namespace Modules\Inventory\Application\Services;

use Modules\Governance\Domain\Models\AuditLog;
use Modules\Inventory\Application\Contracts\InventoryServiceInterface;
use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Domain\Contracts\ProductRepositoryInterface;
use Modules\Inventory\Domain\Contracts\StockRepositoryInterface;
use Modules\Inventory\Domain\Models\StockLevel;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService implements InventoryServiceInterface
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected StockRepositoryInterface $stockRepository,
    ) {}

    public function adjustStock(StockAdjustmentDTO $dto): StockLevel
    {
        return DB::transaction(function () use ($dto) {
            $product = $this->productRepository->findOrFail($dto->productId);
            $stockLevel = $this->stockRepository->getOrCreateStockLevel($dto->productId, $dto->warehouseId);
            $qty = abs($dto->quantity);
            $cost = $dto->unitCost ?? (float) $product->purchase_price;

            $isInflow = in_array($dto->type, ['adjustment_in', 'production_receipt', 'purchase_receipt']);

            if ($isInflow) {
                $stockLevel->quantity_on_hand += $qty;
                $stockLevel->quantity_available += $qty;
            } else {
                if ($stockLevel->quantity_available < $qty) {
                    throw new InvalidArgumentException("Insufficient available stock for product [{$product->sku}].");
                }
                $stockLevel->quantity_on_hand -= $qty;
                $stockLevel->quantity_available -= $qty;
            }

            $stockLevel->save();

            $this->stockRepository->recordMovement([
                'product_id' => $product->id,
                'warehouse_id' => $dto->warehouseId,
                'movement_type' => $dto->type,
                'reference_type' => $dto->referenceType,
                'reference_id' => $dto->referenceId,
                'quantity' => $isInflow ? $qty : -$qty,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
                'notes' => $dto->notes,
                'created_by' => $dto->userId,
            ]);

            AuditLog::log('adjusted_stock', $stockLevel, null, ['type' => $dto->type, 'qty' => $qty]);

            return $stockLevel;
        });
    }

    public function hasAvailableStock(int $productId, int $warehouseId, float $requiredQuantity): bool
    {
        $level = $this->stockRepository->getStockLevel($productId, $warehouseId);
        return $level && $level->quantity_available >= $requiredQuantity;
    }
}
