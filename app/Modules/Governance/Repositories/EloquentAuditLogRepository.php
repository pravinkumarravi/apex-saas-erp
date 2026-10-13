<?php

namespace Modules\Governance\Repositories;

use Modules\Governance\Contracts\AuditLogRepositoryInterface;
use Modules\Governance\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = AuditLog::with('user');

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('auditable_type', 'like', "%{$s}%")
                    ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    public function log(string $action, mixed $model, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::log($action, $model, $oldValues, $newValues);
    }
}
