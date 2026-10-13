<?php

namespace Modules\HumanResources\Models;

use Modules\HumanResources\Contracts\Employee as EmployeeContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model implements EmployeeContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'employees';

    protected $fillable = [
        'tenant_id',
        'emp_code',
        'user_id',
        'department_id',
        'designation_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'date_of_joining',
        'basic_salary',
        'hra',
        'special_allowance',
        'pan',
        'aadhar',
        'pf_number',
        'esi_number',
        'bank_account',
        'bank_ifsc',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'special_allowance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'employee_id');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'employee_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getGrossSalaryAttribute(): float
    {
        return (float) ($this->basic_salary + $this->hra + $this->special_allowance);
    }
}
