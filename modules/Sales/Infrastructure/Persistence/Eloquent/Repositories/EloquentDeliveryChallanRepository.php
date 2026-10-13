<?php

namespace Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Sales\Domain\Contracts\DeliveryChallanRepositoryInterface;
use Modules\Sales\Domain\Models\DeliveryChallan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentDeliveryChallanRepository implements DeliveryChallanRepositoryInterface
{
    public function findById(int $id): ?DeliveryChallan
    {
        return DeliveryChallan::with(['salesOrder.customer', 'warehouse', 'dispatcher'])->find($id);
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return DeliveryChallan::with(['salesOrder.customer', 'warehouse', 'dispatcher'])
            ->latest('dispatch_date')
            ->paginate($perPage);
    }

    public function create(array $data): DeliveryChallan
    {
        return DeliveryChallan::create($data);
    }
}
