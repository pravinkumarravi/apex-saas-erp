<?php

namespace Modules\Governance\Domain\Models;

use Modules\Governance\Domain\Contracts\ApprovalWorkflow as ApprovalWorkflowContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model implements ApprovalWorkflowContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'module',
        'min_amount',
        'max_amount',
        'steps',
        'is_active',
    ];

    protected $casts = [
        'steps' => 'array',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
