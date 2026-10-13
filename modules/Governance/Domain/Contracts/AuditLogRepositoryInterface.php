<?php

namespace Modules\Governance\Domain\Contracts;

use Modules\Governance\Domain\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;
    public function log(string $action, mixed $model, ?array $oldValues = null, ?array $newValues = null): AuditLog;
}
