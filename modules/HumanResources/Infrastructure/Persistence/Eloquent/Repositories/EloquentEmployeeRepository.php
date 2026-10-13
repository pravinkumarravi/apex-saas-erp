<?php

namespace Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\HumanResources\Domain\Contracts\Employee;
use Modules\HumanResources\Domain\Contracts\EmployeeRepositoryInterface;
use Modules\HumanResources\Domain\Models\EmployeeProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function all(): Collection
    {
        return EmployeeProxy::with(['department', 'designation'])->get();
    }

    public function find(int $id): ?Employee
    {
        return EmployeeProxy::with(['department', 'designation'])->find($id);
    }

    public function findByEmpCode(string $code): ?Employee
    {
        return EmployeeProxy::where('emp_code', $code)->with(['department', 'designation'])->first();
    }

    public function create(array $data): Employee
    {
        return EmployeeProxy::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $employee = $this->find($id);
        return $employee ? $employee->update($data) : false;
    }
}
