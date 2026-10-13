<?php

namespace Modules\Assets\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AssetCategoryRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?AssetCategory;
    public function create(array $data): AssetCategory;
}
