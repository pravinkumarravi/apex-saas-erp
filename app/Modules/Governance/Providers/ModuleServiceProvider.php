<?php

namespace Modules\Governance\Providers;

use Modules\Governance\Contracts\ApprovalRepositoryInterface;
use Modules\Governance\Contracts\AuditLogRepositoryInterface;
use Modules\Governance\Models\ApprovalRequest;
use Modules\Governance\Models\ApprovalWorkflow;
use Modules\Governance\Models\AuditLog;
use Modules\Governance\Repositories\EloquentApprovalRepository;
use Modules\Governance\Repositories\EloquentAuditLogRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
        $this->app->bind(ApprovalRepositoryInterface::class, EloquentApprovalRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'governance');
            $this->loadViewsFrom($viewsPath, 'governance');
        }
    }
}
