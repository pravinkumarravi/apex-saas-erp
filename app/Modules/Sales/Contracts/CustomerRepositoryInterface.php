<?php

namespace Modules\Sales\Contracts;

use Modules\Sales\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;
    public function findByCode(string $code): ?Customer;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function allActive(): Collection;
    public function create(array $data): Customer;
    public function update(int $id, array $data): Customer;
}
