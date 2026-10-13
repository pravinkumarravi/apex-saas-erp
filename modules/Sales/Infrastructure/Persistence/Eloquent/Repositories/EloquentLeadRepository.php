<?php

namespace Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Sales\Domain\Contracts\LeadRepositoryInterface;
use Modules\Sales\Domain\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentLeadRepository implements LeadRepositoryInterface
{
    public function findById(int $id): ?Lead
    {
        return Lead::with('assignee')->find($id);
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Lead::with('assignee')->latest()->paginate($perPage);
    }

    public function create(array $data): Lead
    {
        return Lead::create($data);
    }
}
