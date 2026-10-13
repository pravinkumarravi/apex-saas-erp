<?php

namespace Modules\Accounting\Models;

use Modules\Accounting\Contracts\Payment as PaymentContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model implements PaymentContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'payment_number',
        'tax_invoice_id',
        'party_type', // customer, supplier
        'party_id',
        'payment_date',
        'amount',
        'payment_type', // receive, pay
        'payment_method', // bank_transfer, cheque, cash, upi, credit_card, other
        'reference_number',
        'account_id',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function taxInvoice(): BelongsTo
    {
        return $this->belongsTo(TaxInvoice::class, 'tax_invoice_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'recorded_by');
    }
}
