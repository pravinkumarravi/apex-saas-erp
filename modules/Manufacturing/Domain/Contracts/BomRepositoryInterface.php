<?php

namespace Modules\Manufacturing\Domain\Contracts;

use Modules\Manufacturing\Domain\Models\BillOfMaterial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BomRepositoryInterface
{
    public function findById(int $id): ?BillOfMaterial;
    public function findOrFail(int $id): BillOfMaterial;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function create(array $data, array $items = []): BillOfMaterial;
}
