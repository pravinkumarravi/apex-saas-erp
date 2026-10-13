<?php

namespace Modules\Inventory\Repositories;

use Modules\Inventory\Contracts\ProductRepositoryInterface;
use Modules\Inventory\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::query()->find($id);
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::query()->where('sku', $sku)->first();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['category', 'uom', 'stockLevels.warehouse']);

        if (!empty($filters['type'])) {
            $query->where('product_type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
                    ->orWhere('hsn_code', 'like', "%{$s}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Product::where('is_active', true)->get();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function delete(int $id): bool
    {
        $product = $this->findOrFail($id);
        return (bool) $product->delete();
    }
}
