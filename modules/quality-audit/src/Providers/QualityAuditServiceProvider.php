<?php

namespace Modules\QualityAudit\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\QualityAudit\Livewire\QualityAuditManager;
use Modules\QualityAudit\Repositories\Contracts\QualityAuditRepositoryInterface;
use Modules\QualityAudit\Repositories\QualityAuditRepository;

class QualityAuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QualityAuditRepositoryInterface::class, QualityAuditRepository::class);
    }

    public function boot(): void
    {
        if (class_exists(Livewire::class)) {
            Livewire::component('quality-audit-manager', QualityAuditManager::class);
        }
    }
}
