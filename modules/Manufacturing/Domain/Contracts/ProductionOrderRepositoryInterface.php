<?php

namespace Modules\Manufacturing\Domain\Contracts;

use Modules\Manufacturing\Domain\Models\ProductionOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductionOrderRepositoryInterface
{
    public function findById(int $id): ?ProductionOrder;
    public function findOrFail(int $id): ProductionOrder;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function create(array $data, array $consumptions = []): ProductionOrder;
    public function updateStatus(int $id, string $status, ?float $producedQuantity = null): ProductionOrder;
}
