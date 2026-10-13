<?php

namespace Modules\Projects\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Projects\Domain\Contracts\ProjectTask;
use Modules\Projects\Domain\Contracts\ProjectTaskRepositoryInterface;
use Modules\Projects\Domain\Models\ProjectTaskProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectTaskRepository implements ProjectTaskRepositoryInterface
{
    public function all(): Collection
    {
        return ProjectTaskProxy::with(['project', 'assignee'])->get();
    }

    public function find(int $id): ?ProjectTask
    {
        return ProjectTaskProxy::with(['project', 'assignee'])->find($id);
    }

    public function getByProject(int $projectId): Collection
    {
        return ProjectTaskProxy::where('project_id', $projectId)->with('assignee')->get();
    }

    public function create(array $data): ProjectTask
    {
        return ProjectTaskProxy::create($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $task = $this->find($id);
        return $task ? $task->update(['status' => $status]) : false;
    }
}
