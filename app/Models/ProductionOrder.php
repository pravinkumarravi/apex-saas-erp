<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'product_id',
        'bom_id',
        'source_warehouse_id',
        'target_warehouse_id',
        'target_quantity',
        'produced_quantity',
        'start_date',
        'due_date',
        'status', // planned, in_progress, qc_pending, completed, cancelled
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_quantity' => 'decimal:4',
        'produced_quantity' => 'decimal:4',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    public function materialConsumptions(): HasMany
    {
        return $this->hasMany(ProductionMaterialConsumption::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
