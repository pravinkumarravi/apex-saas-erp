<?php

namespace Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\HumanResources\Domain\Contracts\Department;
use Modules\HumanResources\Domain\Contracts\DepartmentRepositoryInterface;
use Modules\HumanResources\Domain\Models\DepartmentProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function all(): Collection
    {
        return DepartmentProxy::with('designations')->get();
    }

    public function find(int $id): ?Department
    {
        return DepartmentProxy::with(['designations', 'employees'])->find($id);
    }

    public function create(array $data): Department
    {
        return DepartmentProxy::create($data);
    }
}
