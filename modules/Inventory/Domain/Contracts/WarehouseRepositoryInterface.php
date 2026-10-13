<?php

namespace Modules\Inventory\Domain\Contracts;

use Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

interface WarehouseRepositoryInterface
{
    public function findById(int $id): ?Warehouse;
    public function findByCode(string $code): ?Warehouse;
    public function allActive(): Collection;
    public function create(array $data): Warehouse;
    public function update(int $id, array $data): Warehouse;
}
