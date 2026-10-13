<?php

namespace Modules\Governance\Repositories;

use Modules\Governance\Contracts\ApprovalRepositoryInterface;
use Modules\Governance\Models\ApprovalRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentApprovalRepository implements ApprovalRepositoryInterface
{
    public function findById(int $id): ?ApprovalRequest
    {
        return ApprovalRequest::with(['requester', 'actioner'])->find($id);
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return ApprovalRequest::with(['requester', 'actioner'])->latest()->paginate($perPage);
    }

    public function getPending(): Collection
    {
        return ApprovalRequest::where('status', 'pending')->with('requester')->latest()->get();
    }

    public function create(array $data): ApprovalRequest
    {
        return ApprovalRequest::create($data);
    }

    public function action(int $id, string $status, ?string $comments = null, ?int $userId = null): ApprovalRequest
    {
        $appr = ApprovalRequest::findOrFail($id);
        $appr->update([
            'status' => $status,
            'comments' => $comments,
            'actioned_by' => $userId,
            'actioned_at' => now(),
        ]);

        return $appr;
    }
}
