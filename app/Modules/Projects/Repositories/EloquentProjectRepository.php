<?php

namespace Modules\Projects\Repositories;

use Modules\Projects\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function all(): Collection
    {
        return Project::with(['customer', 'manager', 'tasks'])->get();
    }

    public function find(int $id): ?Project
    {
        return Project::with(['customer', 'manager', 'tasks'])->find($id);
    }

    public function findByCode(string $code): ?Project
    {
        return Project::where('code', $code)->with(['customer', 'manager', 'tasks'])->first();
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $project = $this->find($id);
        return $project ? $project->update($data) : false;
    }
}
