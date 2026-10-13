<?php

namespace Modules\Identity\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Identity\Domain\Contracts\RoleRepositoryInterface;
use Modules\Identity\Domain\Models\Role;
use Illuminate\Database\Eloquent\Collection;

final class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function findById(int $id): ?Role
    {
        return Role::query()->find($id);
    }

    public function findBySlug(string $slug): ?Role
    {
        return Role::query()->where('slug', $slug)->first();
    }

    public function all(): Collection
    {
        return Role::with(['permissions', 'users'])->get();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }
}
