<?php

namespace Modules\Sales\Models;

use Modules\Sales\Contracts\Lead as LeadContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model implements LeadContract
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'contact_name',
        'company_name',
        'email',
        'phone',
        'stage', // new, contacted, qualified, proposal, negotiation, won, lost
        'expected_value',
        'expected_close_date',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'expected_close_date' => 'date',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\Modules\Identity\Models\User::class, 'assigned_to');
    }
}
