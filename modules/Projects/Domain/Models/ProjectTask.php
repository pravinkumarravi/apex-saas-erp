<?php

namespace Modules\Projects\Domain\Models;

use Modules\Projects\Domain\Contracts\ProjectTask as ProjectTaskContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTask extends Model implements ProjectTaskContract
{
    use HasFactory;

    protected $table = 'project_tasks';

    protected $fillable = [
        'project_id',
        'title',
        'assigned_to',
        'start_date',
        'due_date',
        'status', // todo, in_progress, review, completed
        'priority', // low, medium, high, urgent
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\Modules\HumanResources\Domain\Models\Employee::class, 'assigned_to');
    }
}
