<?php

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Contracts\Payment;
use Modules\Accounting\Domain\Contracts\PaymentRepositoryInterface;
use Modules\Accounting\Domain\Models\PaymentProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function all(): Collection
    {
        return PaymentProxy::all();
    }

    public function find(int $id): ?Payment
    {
        return PaymentProxy::find($id);
    }

    public function create(array $data): Payment
    {
        return PaymentProxy::create($data);
    }
}
