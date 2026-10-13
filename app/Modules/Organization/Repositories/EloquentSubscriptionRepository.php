<?php

namespace Modules\Organization\Repositories;

use Modules\Organization\Contracts\SubscriptionRepositoryInterface;
use Modules\Organization\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

final class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function getActiveForTenant(int $tenantId): ?Subscription
    {
        return Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->latest()
            ->first();
    }

    public function allForTenant(int $tenantId): Collection
    {
        return Subscription::where('tenant_id', $tenantId)
            ->latest()
            ->get();
    }

    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }
}
