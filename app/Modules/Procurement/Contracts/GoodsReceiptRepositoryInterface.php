<?php

namespace Modules\Procurement\Contracts;

use Modules\Procurement\Models\GoodsReceiptNote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GoodsReceiptRepositoryInterface
{
    public function findById(int $id): ?GoodsReceiptNote;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function create(array $data, array $items = []): GoodsReceiptNote;
}
