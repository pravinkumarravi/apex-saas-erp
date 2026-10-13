<?php

namespace Modules\HumanResources\Repositories;

use Modules\HumanResources\Contracts\DepartmentRepositoryInterface;
use Modules\HumanResources\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function all(): Collection
    {
        return Department::with('designations')->get();
    }

    public function find(int $id): ?Department
    {
        return Department::with(['designations', 'employees'])->find($id);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }
}
