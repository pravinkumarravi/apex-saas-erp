<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type', // asset, liability, equity, revenue, expense
        'subtype',
        'parent_id',
        'description',
        'is_active',
        'is_reconciled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_reconciled' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalItems(): HasMany
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }

    public function getBalanceAttribute(): float
    {
        $debit = $this->journalItems()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted');
        })->sum('debit');

        $credit = $this->journalItems()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted');
        })->sum('credit');

        // For Assets and Expenses: normal balance is Debit - Credit
        // For Liabilities, Equity, and Revenue: normal balance is Credit - Debit
        if (in_array($this->type, ['asset', 'expense'])) {
            return (float) ($debit - $credit);
        }

        return (float) ($credit - $debit);
    }
}
