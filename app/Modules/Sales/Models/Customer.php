<?php

namespace Modules\Sales\Models;

use Modules\Sales\Contracts\Customer as CustomerContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model implements CustomerContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'company_name',
        'email',
        'phone',
        'gstin',
        'pan',
        'state_code',
        'billing_address',
        'shipping_address',
        'city',
        'state',
        'pincode',
        'credit_limit',
        'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(\Modules\Accounting\Models\TaxInvoice::class, 'party_id')->where('party_type', 'customer');
    }
}
