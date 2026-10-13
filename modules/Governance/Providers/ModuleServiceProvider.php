<?php

namespace Modules\Governance\Providers;

use Modules\Governance\Domain\Contracts\ApprovalRepositoryInterface;
use Modules\Governance\Domain\Contracts\AuditLogRepositoryInterface;
use Modules\Governance\Domain\Models\ApprovalRequest;
use Modules\Governance\Domain\Models\ApprovalWorkflow;
use Modules\Governance\Domain\Models\AuditLog;
use Modules\Governance\Infrastructure\Persistence\Eloquent\Repositories\EloquentApprovalRepository;
use Modules\Governance\Infrastructure\Persistence\Eloquent\Repositories\EloquentAuditLogRepository;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        AuditLog::class,
        ApprovalWorkflow::class,
        ApprovalRequest::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
        $this->app->bind(ApprovalRepositoryInterface::class, EloquentApprovalRepository::class);
    }
}
