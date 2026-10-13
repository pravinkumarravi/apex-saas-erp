<?php

namespace Modules\Accounting\Domain\Models;

use Modules\Accounting\Domain\Contracts\TaxInvoiceItem as TaxInvoiceItemContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxInvoiceItem extends Model implements TaxInvoiceItemContract
{
    use HasFactory;

    protected $table = 'tax_invoice_items';

    protected $fillable = [
        'tax_invoice_id',
        'product_id',
        'item_name',
        'hsn_code',
        'quantity',
        'unit_price',
        'discount_percent',
        'gst_rate',
        'taxable_value',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'taxable_value' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function taxInvoice(): BelongsTo
    {
        return $this->belongsTo(TaxInvoice::class, 'tax_invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Domain\Models\Product::class, 'product_id');
    }
}
