<?php

namespace Modules\HumanResources\Models;

use Modules\HumanResources\Contracts\Attendance as AttendanceContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model implements AttendanceContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'attendances';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'status', // present, absent, half_day, on_leave, holiday
        'clock_in',
        'clock_out',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
