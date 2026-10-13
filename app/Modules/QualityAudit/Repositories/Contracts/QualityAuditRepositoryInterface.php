<?php

namespace Modules\QualityAudit\Repositories\Contracts;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface QualityAuditRepositoryInterface extends RepositoryInterface
{
    /**
     * Get audits by type.
     */
    public function getByType(string $type): Collection;

    /**
     * Get audits pending CAPA action.
     */
    public function getPendingCapa(): Collection;
}
