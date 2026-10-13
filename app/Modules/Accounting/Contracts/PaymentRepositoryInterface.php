<?php

namespace Modules\Accounting\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Payment;
    public function create(array $data): Payment;
}
