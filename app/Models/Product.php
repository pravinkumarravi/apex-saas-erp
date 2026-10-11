<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'uom_id',
        'sku',
        'barcode',
        'name',
        'description',
        'hsn_code',
        'product_type', // raw_material, work_in_progress, finished_good, consumable, service
        'purchase_price',
        'sales_price',
        'gst_rate',
        'reorder_level',
        'safety_stock',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sales_price' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function billsOfMaterial(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function getTotalStockOnHandAttribute(): float
    {
        return (float) $this->stockLevels->sum('quantity_on_hand');
    }

    public function getTotalStockAvailableAttribute(): float
    {
        return (float) $this->stockLevels->sum('quantity_available');
    }
}
