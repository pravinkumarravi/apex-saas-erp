<?php

namespace Modules\Manufacturing\Repositories;

use Modules\Manufacturing\Contracts\WorkCenterRepositoryInterface;
use Modules\Manufacturing\Models\WorkCenter;
use Illuminate\Database\Eloquent\Collection;

final class EloquentWorkCenterRepository implements WorkCenterRepositoryInterface
{
    public function findById(int $id): ?WorkCenter
    {
        return WorkCenter::query()->find($id);
    }

    public function allActive(): Collection
    {
        return WorkCenter::where('is_active', true)->get();
    }

    public function create(array $data): WorkCenter
    {
        return WorkCenter::create($data);
    }
}
