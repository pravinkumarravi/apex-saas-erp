<?php

namespace Modules\Organization\Contracts;

use Modules\Organization\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionRepositoryInterface
{
    public function getActiveForTenant(int $tenantId): ?Subscription;
    public function allForTenant(int $tenantId): Collection;
    public function create(array $data): Subscription;
}
