<?php

namespace Modules\HumanResources\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface LeaveApplicationRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?LeaveApplication;
    public function create(array $data): LeaveApplication;
    public function updateStatus(int $id, string $status, int $approverId): bool;
}
