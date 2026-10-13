<?php

namespace Modules\Inventory\Domain\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Inventory\Domain\Contracts\StockMovement as StockMovementContract;

class StockMovement extends Model implements StockMovementContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'warehouse_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'batch_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Domain\Models\User::class, 'created_by');
    }
}
