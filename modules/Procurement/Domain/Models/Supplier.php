<?php

namespace Modules\Procurement\Domain\Models;

use Modules\Procurement\Domain\Contracts\Supplier as SupplierContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model implements SupplierContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'gstin',
        'pan',
        'state_code',
        'address',
        'city',
        'state',
        'pincode',
        'bank_details',
        'status',
    ];

    protected $casts = [
        'bank_details' => 'array',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
