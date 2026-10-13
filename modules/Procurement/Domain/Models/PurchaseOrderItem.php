<?php

namespace Modules\Procurement\Domain\Models;

use Modules\Procurement\Domain\Contracts\PurchaseOrderItem as PurchaseOrderItemContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model implements PurchaseOrderItemContract
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percent',
        'hsn_code',
        'gst_rate',
        'tax_amount',
        'total_amount',
        'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'received_quantity' => 'decimal:4',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Domain\Models\Product::class);
    }
}
