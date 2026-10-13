<?php

namespace Modules\Projects\Models;

use Modules\Projects\Contracts\Project as ProjectContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model implements ProjectContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'projects';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'customer_id',
        'manager_id',
        'start_date',
        'end_date',
        'budget',
        'status', // planning, active, on_hold, completed, cancelled
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Modules\Sales\Models\Customer::class, 'customer_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\Modules\HumanResources\Models\Employee::class, 'manager_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }
}
