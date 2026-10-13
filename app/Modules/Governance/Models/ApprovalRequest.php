<?php

namespace Modules\Governance\Models;

use Modules\Governance\Contracts\ApprovalRequest as ApprovalRequestContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model implements ApprovalRequestContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'approvable_type',
        'approvable_id',
        'current_step',
        'total_steps',
        'status',
        'comments',
        'requested_by',
        'actioned_by',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'requested_by');
    }

    public function actioner(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'actioned_by');
    }
}
