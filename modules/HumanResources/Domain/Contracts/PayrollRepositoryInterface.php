<?php

namespace Modules\HumanResources\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PayrollRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?PayrollRun;
    public function createRun(array $data, array $payslips = []): PayrollRun;
    public function updateStatus(int $id, string $status): bool;
}
