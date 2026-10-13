<?php

namespace Modules\Inventory\Repositories;

use Modules\Inventory\Contracts\WarehouseRepositoryInterface;
use Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

final class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function findById(int $id): ?Warehouse
    {
        return Warehouse::query()->find($id);
    }

    public function findByCode(string $code): ?Warehouse
    {
        return Warehouse::query()->where('code', $code)->first();
    }

    public function allActive(): Collection
    {
        return Warehouse::withCount('stockLevels')
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    public function update(int $id, array $data): Warehouse
    {
        $wh = Warehouse::query()->findOrFail($id);
        $wh->update($data);
        return $wh;
    }
}
