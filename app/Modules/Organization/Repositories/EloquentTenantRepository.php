<?php

namespace Modules\Organization\Repositories;

use Modules\Organization\Contracts\TenantRepositoryInterface;
use Modules\Organization\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

final class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant
    {
        return Tenant::query()->find($id);
    }

    public function findBySubdomain(string $subdomain): ?Tenant
    {
        return Tenant::query()->where('subdomain', $subdomain)->first();
    }

    public function all(): Collection
    {
        return Tenant::all();
    }

    public function update(int $id, array $data): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($data);
        return $tenant;
    }
}
