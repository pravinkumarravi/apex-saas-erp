<?php

namespace Modules\RestApi\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebhookSubscription extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'webhook_subscriptions';

    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'target_url',
        'secret',
        'events',
        'is_active',
        'last_dispatched_at',
        'failure_count',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_dispatched_at' => 'datetime',
        'failure_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($subscription) {
            if (empty($subscription->secret)) {
                $subscription->secret = 'whsec_' . Str::random(32);
            }
        });
    }

    public function listensTo(string $event): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $events = $this->events ?? ['*'];

        if (in_array('*', $events, true)) {
            return true;
        }

        return in_array($event, $events, true);
    }
}
