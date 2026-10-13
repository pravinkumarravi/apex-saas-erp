<?php

namespace Modules\HumanResources\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Department;
    public function create(array $data): Department;
}
