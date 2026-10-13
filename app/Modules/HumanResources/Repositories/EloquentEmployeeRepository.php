<?php

namespace Modules\HumanResources\Repositories;

use Modules\HumanResources\Contracts\EmployeeRepositoryInterface;
use Modules\HumanResources\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function all(): Collection
    {
        return Employee::with(['department', 'designation'])->get();
    }

    public function find(int $id): ?Employee
    {
        return Employee::with(['department', 'designation'])->find($id);
    }

    public function findByEmpCode(string $code): ?Employee
    {
        return Employee::where('emp_code', $code)->with(['department', 'designation'])->first();
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $employee = $this->find($id);
        return $employee ? $employee->update($data) : false;
    }
}
