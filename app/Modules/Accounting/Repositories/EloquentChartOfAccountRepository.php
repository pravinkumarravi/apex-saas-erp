<?php

namespace Modules\Accounting\Repositories;

use Modules\Accounting\Contracts\ChartOfAccountRepositoryInterface;
use Modules\Accounting\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Collection;

class EloquentChartOfAccountRepository implements ChartOfAccountRepositoryInterface
{
    public function all(): Collection
    {
        return ChartOfAccount::all();
    }

    public function find(int $id): ?ChartOfAccount
    {
        return ChartOfAccount::find($id);
    }

    public function findByCode(string $code): ?ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)->first();
    }

    public function create(array $data): ChartOfAccount
    {
        return ChartOfAccount::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $account = $this->find($id);
        return $account ? $account->update($data) : false;
    }
}
