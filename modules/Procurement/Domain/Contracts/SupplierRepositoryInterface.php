<?php

namespace Modules\Procurement\Domain\Contracts;

use Modules\Procurement\Domain\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface
{
    public function findById(int $id): ?Supplier;
    public function findByCode(string $code): ?Supplier;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function all(): Collection;
    public function create(array $data): Supplier;
    public function update(int $id, array $data): Supplier;
}
