<?php

namespace Modules\HumanResources\Models;

use Modules\HumanResources\Contracts\Department as DepartmentContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model implements DepartmentContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'departments';

    protected $fillable = [
        'tenant_id',
        'name',
    ];

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
