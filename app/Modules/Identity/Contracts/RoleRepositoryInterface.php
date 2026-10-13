<?php

namespace Modules\Identity\Contracts;

use Modules\Identity\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;
    public function findBySlug(string $slug): ?Role;
    public function all(): Collection;
    public function create(array $data): Role;
}
