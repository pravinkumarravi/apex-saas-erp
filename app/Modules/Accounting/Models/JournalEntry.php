<?php

namespace Modules\Accounting\Models;

use Modules\Accounting\Contracts\JournalEntry as JournalEntryContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model implements JournalEntryContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'journal_entries';

    protected $fillable = [
        'tenant_id',
        'entry_number',
        'entry_date',
        'reference',
        'description',
        'source_type',
        'source_id',
        'status', // draft, posted, void
        'posted_at',
        'posted_by',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(JournalItem::class, 'journal_entry_id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'posted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'created_by');
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->items->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->items->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.001;
    }
}
