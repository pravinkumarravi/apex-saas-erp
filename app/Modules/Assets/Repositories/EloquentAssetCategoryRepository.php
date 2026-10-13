<?php

namespace Modules\Assets\Repositories;

use Modules\Assets\Contracts\AssetCategoryRepositoryInterface;
use Modules\Assets\Models\AssetCategory;
use Illuminate\Database\Eloquent\Collection;

class EloquentAssetCategoryRepository implements AssetCategoryRepositoryInterface
{
    public function all(): Collection
    {
        return AssetCategory::all();
    }

    public function find(int $id): ?AssetCategory
    {
        return AssetCategory::find($id);
    }

    public function create(array $data): AssetCategory
    {
        return AssetCategory::create($data);
    }
}
