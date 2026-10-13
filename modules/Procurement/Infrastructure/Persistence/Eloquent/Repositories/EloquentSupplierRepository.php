<?php

namespace Modules\Procurement\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Procurement\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Procurement\Domain\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentSupplierRepository implements SupplierRepositoryInterface
{
    public function findById(int $id): ?Supplier
    {
        return Supplier::query()->find($id);
    }

    public function findByCode(string $code): ?Supplier
    {
        return Supplier::query()->where('code', $code)->first();
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Supplier::query();

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function all(): Collection
    {
        return Supplier::all();
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }
}
