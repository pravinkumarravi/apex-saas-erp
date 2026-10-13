<?php

namespace Modules\Accounting\Domain\Models;

use Modules\Accounting\Domain\Contracts\FiscalYear as FiscalYearContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model implements FiscalYearContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'fiscal_years';

    protected $fillable = [
        'tenant_id',
        'title',
        'start_date',
        'end_date',
        'is_closed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
    ];
}
