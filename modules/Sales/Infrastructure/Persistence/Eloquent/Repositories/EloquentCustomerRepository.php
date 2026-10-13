<?php

namespace Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Sales\Domain\Contracts\CustomerRepositoryInterface;
use Modules\Sales\Domain\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer
    {
        return Customer::withCount('salesOrders')->find($id);
    }

    public function findByCode(string $code): ?Customer
    {
        return Customer::where('code', $code)->first();
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Customer::withCount('salesOrders');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Customer::where('status', 'active')->get();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $cust = Customer::findOrFail($id);
        $cust->update($data);
        return $cust;
    }
}
