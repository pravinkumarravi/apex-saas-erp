<?php

namespace Modules\HumanResources\Domain\Models;

use Modules\HumanResources\Domain\Contracts\LeaveApplication as LeaveApplicationContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApplication extends Model implements LeaveApplicationContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'leave_applications';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'leave_type', // casual, sick, earned, unpaid, maternity, paternity
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status', // pending, approved, rejected
        'approved_by',
        'actioned_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:1',
        'actioned_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Domain\Models\User::class, 'approved_by');
    }
}
