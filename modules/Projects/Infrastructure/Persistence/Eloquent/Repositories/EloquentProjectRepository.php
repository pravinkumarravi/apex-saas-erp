<?php

namespace Modules\Projects\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Projects\Domain\Contracts\Project;
use Modules\Projects\Domain\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Domain\Models\ProjectProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function all(): Collection
    {
        return ProjectProxy::with(['customer', 'manager', 'tasks'])->get();
    }

    public function find(int $id): ?Project
    {
        return ProjectProxy::with(['customer', 'manager', 'tasks'])->find($id);
    }

    public function findByCode(string $code): ?Project
    {
        return ProjectProxy::where('code', $code)->with(['customer', 'manager', 'tasks'])->first();
    }

    public function create(array $data): Project
    {
        return ProjectProxy::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $project = $this->find($id);
        return $project ? $project->update($data) : false;
    }
}
