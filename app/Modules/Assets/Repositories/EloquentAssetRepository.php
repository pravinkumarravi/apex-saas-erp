<?php

namespace Modules\Assets\Repositories;

use Modules\Assets\Contracts\AssetRepositoryInterface;
use Modules\Assets\Models\Asset;
use Illuminate\Database\Eloquent\Collection;

class EloquentAssetRepository implements AssetRepositoryInterface
{
    public function all(): Collection
    {
        return Asset::with('category')->get();
    }

    public function find(int $id): ?Asset
    {
        return Asset::with('category')->find($id);
    }

    public function findByTag(string $tag): ?Asset
    {
        return Asset::where('asset_tag', $tag)->with('category')->first();
    }

    public function create(array $data): Asset
    {
        return Asset::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $asset = $this->find($id);
        return $asset ? $asset->update($data) : false;
    }
}
