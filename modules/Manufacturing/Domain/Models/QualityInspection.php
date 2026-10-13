<?php

namespace Modules\Manufacturing\Domain\Models;

use Modules\Manufacturing\Domain\Contracts\QualityInspection as QualityInspectionContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspection extends Model implements QualityInspectionContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'inspection_number',
        'inspectable_type',
        'inspectable_id',
        'inspector_id',
        'sample_size',
        'passed_quantity',
        'rejected_quantity',
        'inspection_date',
        'status', // pass, fail, rework
        'checkpoints',
        'remarks',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'sample_size' => 'decimal:4',
        'passed_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'checkpoints' => 'array',
    ];

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Domain\Models\User::class, 'inspector_id');
    }
}
