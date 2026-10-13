<?php

namespace Modules\Manufacturing\Models;

use Modules\Manufacturing\Contracts\BillOfMaterial as BillOfMaterialContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillOfMaterial extends Model implements BillOfMaterialContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'bills_of_material';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'bom_number',
        'version',
        'quantity',
        'estimated_cost',
        'routing_instructions',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'estimated_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class, 'bom_id');
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'bom_id');
    }
}
