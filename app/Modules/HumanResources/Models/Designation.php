<?php

namespace Modules\HumanResources\Models;

use Modules\HumanResources\Contracts\Designation as DesignationContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model implements DesignationContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'designations';

    protected $fillable = [
        'tenant_id',
        'title',
        'department_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }
}
