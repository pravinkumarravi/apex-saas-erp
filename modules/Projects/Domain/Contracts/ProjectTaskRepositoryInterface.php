<?php

namespace Modules\Projects\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ProjectTaskRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?ProjectTask;
    public function getByProject(int $projectId): Collection;
    public function create(array $data): ProjectTask;
    public function updateStatus(int $id, string $status): bool;
}
