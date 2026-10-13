<?php

namespace Modules\Organization\Contracts;

use Modules\Organization\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

interface TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant;
    public function findBySubdomain(string $subdomain): ?Tenant;
    public function all(): Collection;
    public function update(int $id, array $data): Tenant;
}
