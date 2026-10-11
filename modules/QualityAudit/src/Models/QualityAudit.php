<?php

namespace Modules\QualityAudit\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class QualityAudit extends Model
{
    use BelongsToTenant;

    protected $table = 'quality_audits';

    protected $fillable = [
        'tenant_id',
        'audit_number',
        'title',
        'audit_type',
        'auditor_name',
        'score_percentage',
        'status',
        'findings',
        'scheduled_date',
    ];

    protected $casts = [
        'score_percentage' => 'float',
        'scheduled_date' => 'date',
    ];
}
