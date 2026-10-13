<?php

namespace Modules\Procurement\Domain\Models;

use Modules\Procurement\Domain\Contracts\GoodsReceiptNote as GoodsReceiptNoteContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptNote extends Model implements GoodsReceiptNoteContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'grn_number',
        'purchase_order_id',
        'warehouse_id',
        'receipt_date',
        'delivery_challan_no',
        'remarks',
        'received_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Domain\Models\Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Domain\Models\User::class, 'received_by');
    }
}
