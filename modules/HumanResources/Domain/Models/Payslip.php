<?php

namespace Modules\HumanResources\Domain\Models;

use Modules\HumanResources\Domain\Contracts\Payslip as PayslipContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model implements PayslipContract
{
    use HasFactory;

    protected $table = 'payslips';

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_pay',
        'hra',
        'special_allowance',
        'gross_salary',
        'pf_deduction',
        'esi_deduction',
        'pt_deduction',
        'tds_deduction',
        'total_deductions',
        'net_salary',
        'payment_status', // unpaid, paid
    ];

    protected $casts = [
        'basic_pay' => 'decimal:2',
        'hra' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'esi_deduction' => 'decimal:2',
        'pt_deduction' => 'decimal:2',
        'tds_deduction' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
