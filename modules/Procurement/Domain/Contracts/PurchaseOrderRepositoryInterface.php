<?php

namespace Modules\Procurement\Domain\Contracts;

use Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PurchaseOrderRepositoryInterface
{
    public function findById(int $id): ?PurchaseOrder;
    public function findOrFail(int $id): PurchaseOrder;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function create(array $data, array $items = []): PurchaseOrder;
    public function updateStatus(int $id, string $status, ?int $approvedBy = null): PurchaseOrder;
}
