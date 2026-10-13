<?php

namespace Modules\Manufacturing\Domain\Models;

use Modules\Manufacturing\Domain\Contracts\ProductionMaterialConsumption as ProductionMaterialConsumptionContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionMaterialConsumption extends Model implements ProductionMaterialConsumptionContract
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'product_id',
        'warehouse_id',
        'planned_quantity',
        'actual_consumed_quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'actual_consumed_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Domain\Models\Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Domain\Models\Warehouse::class);
    }
}
