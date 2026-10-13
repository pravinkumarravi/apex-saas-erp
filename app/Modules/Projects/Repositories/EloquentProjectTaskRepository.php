<?php

namespace Modules\Projects\Repositories;

use Modules\Projects\Contracts\ProjectTaskRepositoryInterface;
use Modules\Projects\Models\ProjectTask;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectTaskRepository implements ProjectTaskRepositoryInterface
{
    public function all(): Collection
    {
        return ProjectTask::with(['project', 'assignee'])->get();
    }

    public function find(int $id): ?ProjectTask
    {
        return ProjectTask::with(['project', 'assignee'])->find($id);
    }

    public function getByProject(int $projectId): Collection
    {
        return ProjectTask::where('project_id', $projectId)->with('assignee')->get();
    }

    public function create(array $data): ProjectTask
    {
        return ProjectTask::create($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $task = $this->find($id);
        return $task ? $task->update(['status' => $status]) : false;
    }
}
