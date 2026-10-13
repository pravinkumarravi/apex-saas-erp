<?php

namespace Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\HumanResources\Domain\Contracts\LeaveApplication;
use Modules\HumanResources\Domain\Contracts\LeaveApplicationRepositoryInterface;
use Modules\HumanResources\Domain\Models\LeaveApplicationProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentLeaveApplicationRepository implements LeaveApplicationRepositoryInterface
{
    public function all(): Collection
    {
        return LeaveApplicationProxy::with(['employee', 'approver'])->get();
    }

    public function find(int $id): ?LeaveApplication
    {
        return LeaveApplicationProxy::with(['employee', 'approver'])->find($id);
    }

    public function create(array $data): LeaveApplication
    {
        return LeaveApplicationProxy::create($data);
    }

    public function updateStatus(int $id, string $status, int $approverId): bool
    {
        $leave = $this->find($id);
        return $leave ? $leave->update([
            'status' => $status,
            'approved_by' => $approverId,
            'actioned_at' => now(),
        ]) : false;
    }
}
