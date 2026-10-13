<?php

namespace Modules\Assets\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Assets\Domain\Contracts\AssetCategory;
use Modules\Assets\Domain\Contracts\AssetCategoryRepositoryInterface;
use Modules\Assets\Domain\Models\AssetCategoryProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentAssetCategoryRepository implements AssetCategoryRepositoryInterface
{
    public function all(): Collection
    {
        return AssetCategoryProxy::all();
    }

    public function find(int $id): ?AssetCategory
    {
        return AssetCategoryProxy::find($id);
    }

    public function create(array $data): AssetCategory
    {
        return AssetCategoryProxy::create($data);
    }
}
