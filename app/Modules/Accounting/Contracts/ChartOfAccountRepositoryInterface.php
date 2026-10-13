<?php

namespace Modules\Accounting\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ChartOfAccountRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?ChartOfAccount;
    public function findByCode(string $code): ?ChartOfAccount;
    public function create(array $data): ChartOfAccount;
    public function update(int $id, array $data): bool;
}
