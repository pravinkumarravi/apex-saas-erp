<?php

namespace Modules\HumanResources\Repositories;

use Modules\HumanResources\Contracts\LeaveApplicationRepositoryInterface;
use Modules\HumanResources\Models\LeaveApplication;
use Illuminate\Database\Eloquent\Collection;

class EloquentLeaveApplicationRepository implements LeaveApplicationRepositoryInterface
{
    public function all(): Collection
    {
        return LeaveApplication::with(['employee', 'approver'])->get();
    }

    public function find(int $id): ?LeaveApplication
    {
        return LeaveApplication::with(['employee', 'approver'])->find($id);
    }

    public function create(array $data): LeaveApplication
    {
        return LeaveApplication::create($data);
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
