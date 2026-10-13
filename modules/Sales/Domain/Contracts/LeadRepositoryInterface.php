<?php

namespace Modules\Sales\Domain\Contracts;

use Modules\Sales\Domain\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeadRepositoryInterface
{
    public function findById(int $id): ?Lead;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Lead;
}
