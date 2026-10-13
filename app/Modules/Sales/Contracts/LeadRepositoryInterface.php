<?php

namespace Modules\Sales\Contracts;

use Modules\Sales\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeadRepositoryInterface
{
    public function findById(int $id): ?Lead;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Lead;
}
