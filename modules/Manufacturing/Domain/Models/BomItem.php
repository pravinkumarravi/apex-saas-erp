<?php

namespace Modules\Manufacturing\Domain\Models;

use Modules\Manufacturing\Domain\Contracts\BomItem as BomItemContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model implements BomItemContract
{
    use HasFactory;

    protected $fillable = [
        'bom_id',
        'product_id',
        'quantity',
        'scrap_percentage',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'scrap_percentage' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Domain\Models\Product::class);
    }
}
