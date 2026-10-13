<?php

namespace Modules\Sales\Models;

use Modules\Sales\Contracts\DeliveryChallan as DeliveryChallanContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryChallan extends Model implements DeliveryChallanContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'challan_number',
        'sales_order_id',
        'warehouse_id',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'eway_bill_number',
        'dispatch_date',
        'status', // prepared, dispatched, in_transit, delivered
        'notes',
        'dispatched_by',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Warehouse::class);
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'dispatched_by');
    }
}
