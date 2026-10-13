<?php

namespace Modules\Sales\Domain\Models;

use Modules\Sales\Domain\Contracts\SalesOrder as SalesOrderContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model implements SalesOrderContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'so_number',
        'customer_id',
        'warehouse_id',
        'order_date',
        'delivery_date',
        'status', // draft, confirmed, picking, shipped, delivered, cancelled
        'subtotal',
        'tax_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Domain\Models\Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function deliveryChallans(): HasMany
    {
        return $this->hasMany(DeliveryChallan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(\Modules\Accounting\Domain\Models\TaxInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Domain\Models\User::class, 'created_by');
    }
}
