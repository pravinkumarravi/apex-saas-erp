<?php

namespace Modules\Sales\Domain\Contracts;

use Modules\Sales\Domain\Models\DeliveryChallan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeliveryChallanRepositoryInterface
{
    public function findById(int $id): ?DeliveryChallan;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): DeliveryChallan;
}
