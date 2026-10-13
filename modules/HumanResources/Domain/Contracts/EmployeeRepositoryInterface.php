<?php

namespace Modules\HumanResources\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface EmployeeRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Employee;
    public function findByEmpCode(string $code): ?Employee;
    public function create(array $data): Employee;
    public function update(int $id, array $data): bool;
}
