<?php

namespace Modules\Accounting\Domain\Models;

use Modules\Accounting\Domain\Contracts\GstTaxRate as GstTaxRateContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstTaxRate extends Model implements GstTaxRateContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'gst_tax_rates';

    protected $fillable = [
        'tenant_id',
        'code',
        'rate',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'cess_rate',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'cgst_rate' => 'decimal:2',
        'sgst_rate' => 'decimal:2',
        'igst_rate' => 'decimal:2',
        'cess_rate' => 'decimal:2',
    ];
}
