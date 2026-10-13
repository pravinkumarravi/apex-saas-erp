<?php

namespace Modules\Organization\Models;

use Modules\Organization\Contracts\Subscription as SubscriptionContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model implements SubscriptionContract
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_name',
        'amount',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'status',
        'payment_reference',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
