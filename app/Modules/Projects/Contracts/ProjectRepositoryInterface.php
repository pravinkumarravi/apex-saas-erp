<?php

namespace Modules\Projects\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Project;
    public function findByCode(string $code): ?Project;
    public function create(array $data): Project;
    public function update(int $id, array $data): bool;
}
