<?php

namespace Modules\HumanResources\Domain\Models;

use Modules\HumanResources\Domain\Contracts\PayrollRun as PayrollRunContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model implements PayrollRunContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'payroll_runs';

    protected $fillable = [
        'tenant_id',
        'month',
        'year',
        'total_gross',
        'total_deductions',
        'total_net',
        'status', // draft, processed, paid
        'journal_entry_id',
        'processed_by',
    ];

    protected $casts = [
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'payroll_run_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(\Modules\Accounting\Domain\Models\JournalEntry::class, 'journal_entry_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Domain\Models\User::class, 'processed_by');
    }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 10));
    }
}
