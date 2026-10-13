<?php

namespace Modules\Manufacturing\Domain\Contracts;

use Modules\Manufacturing\Domain\Models\WorkCenter;
use Illuminate\Database\Eloquent\Collection;

interface WorkCenterRepositoryInterface
{
    public function findById(int $id): ?WorkCenter;
    public function allActive(): Collection;
    public function create(array $data): WorkCenter;
}
