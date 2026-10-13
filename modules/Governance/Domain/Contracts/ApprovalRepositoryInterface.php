<?php

namespace Modules\Governance\Domain\Contracts;

use Modules\Governance\Domain\Models\ApprovalRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ApprovalRepositoryInterface
{
    public function findById(int $id): ?ApprovalRequest;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function getPending(): Collection;
    public function create(array $data): ApprovalRequest;
    public function action(int $id, string $status, ?string $comments = null, ?int $userId = null): ApprovalRequest;
}
