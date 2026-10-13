<?php

namespace Modules\Accounting\Repositories;

use Modules\Accounting\Contracts\PaymentRepositoryInterface;
use Modules\Accounting\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function all(): Collection
    {
        return Payment::all();
    }

    public function find(int $id): ?Payment
    {
        return Payment::find($id);
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }
}
