<?php

namespace Modules\Sales\Domain\Contracts;

use Modules\Sales\Domain\Models\SalesOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SalesOrderRepositoryInterface
{
    public function findById(int $id): ?SalesOrder;
    public function findOrFail(int $id): SalesOrder;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function create(array $data, array $items = []): SalesOrder;
    public function updateStatus(int $id, string $status): SalesOrder;
}
