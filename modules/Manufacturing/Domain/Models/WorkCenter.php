<?php

namespace Modules\Manufacturing\Domain\Models;

use Modules\Manufacturing\Domain\Contracts\WorkCenter as WorkCenterContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkCenter extends Model implements WorkCenterContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'hourly_rate',
        'capacity_per_day_hours',
        'is_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'capacity_per_day_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
