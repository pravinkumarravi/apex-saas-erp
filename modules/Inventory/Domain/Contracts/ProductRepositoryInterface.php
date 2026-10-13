<?php

namespace Modules\Inventory\Domain\Contracts;

use Modules\Inventory\Domain\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findOrFail(int $id): Product;
    public function findBySku(string $sku): ?Product;
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function allActive(): Collection;
    public function create(array $data): Product;
    public function update(int $id, array $data): Product;
    public function delete(int $id): bool;
}
