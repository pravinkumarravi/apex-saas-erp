<?php

namespace Modules\Manufacturing\Contracts;

use Modules\Manufacturing\Models\WorkCenter;
use Illuminate\Database\Eloquent\Collection;

interface WorkCenterRepositoryInterface
{
    public function findById(int $id): ?WorkCenter;
    public function allActive(): Collection;
    public function create(array $data): WorkCenter;
}
