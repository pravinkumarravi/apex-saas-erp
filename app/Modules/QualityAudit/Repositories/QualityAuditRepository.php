<?php

namespace Modules\QualityAudit\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\QualityAudit\Models\QualityAudit;
use Modules\QualityAudit\Repositories\Contracts\QualityAuditRepositoryInterface;

class QualityAuditRepository extends BaseRepository implements QualityAuditRepositoryInterface
{
    protected function resolveModel(): Model
    {
        return new QualityAudit();
    }

    public function getByType(string $type): Collection
    {
        return $this->model->where('audit_type', $type)->latest('scheduled_date')->get();
    }

    public function getPendingCapa(): Collection
    {
        return $this->model->where('status', 'capa_required')->latest()->get();
    }
}
