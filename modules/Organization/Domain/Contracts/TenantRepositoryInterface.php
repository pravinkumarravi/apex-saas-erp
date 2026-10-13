<?php

namespace Modules\Organization\Domain\Contracts;

use Modules\Organization\Domain\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

interface TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant;
    public function findBySubdomain(string $subdomain): ?Tenant;
    public function all(): Collection;
    public function update(int $id, array $data): Tenant;
}
