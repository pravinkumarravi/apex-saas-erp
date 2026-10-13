<?php

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Contracts\ChartOfAccount;
use Modules\Accounting\Domain\Contracts\ChartOfAccountRepositoryInterface;
use Modules\Accounting\Domain\Models\ChartOfAccountProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentChartOfAccountRepository implements ChartOfAccountRepositoryInterface
{
    public function all(): Collection
    {
        return ChartOfAccountProxy::all();
    }

    public function find(int $id): ?ChartOfAccount
    {
        return ChartOfAccountProxy::find($id);
    }

    public function findByCode(string $code): ?ChartOfAccount
    {
        return ChartOfAccountProxy::where('code', $code)->first();
    }

    public function create(array $data): ChartOfAccount
    {
        return ChartOfAccountProxy::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $account = $this->find($id);
        return $account ? $account->update($data) : false;
    }
}
