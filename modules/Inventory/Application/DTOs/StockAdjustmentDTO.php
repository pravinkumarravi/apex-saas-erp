<?php

namespace Modules\Inventory\Application\DTOs;

final readonly class StockAdjustmentDTO
{
    public function __construct(
        public int $productId,
        public int $warehouseId,
        public string $type, // adjustment_in, adjustment_out, scrap, production_issue, production_receipt, purchase_receipt, sales_dispatch
        public float $quantity,
        public ?float $unitCost = null,
        public ?string $referenceType = 'manual_adjustment',
        public ?int $referenceId = null,
        public ?string $notes = null,
        public ?int $userId = null,
    ) {}
}
