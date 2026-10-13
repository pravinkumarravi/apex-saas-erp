<?php

namespace Modules\Assets\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Assets\Domain\Contracts\Asset;
use Modules\Assets\Domain\Contracts\AssetRepositoryInterface;
use Modules\Assets\Domain\Models\AssetProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentAssetRepository implements AssetRepositoryInterface
{
    public function all(): Collection
    {
        return AssetProxy::with('category')->get();
    }

    public function find(int $id): ?Asset
    {
        return AssetProxy::with('category')->find($id);
    }

    public function findByTag(string $tag): ?Asset
    {
        return AssetProxy::where('asset_tag', $tag)->with('category')->first();
    }

    public function create(array $data): Asset
    {
        return AssetProxy::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $asset = $this->find($id);
        return $asset ? $asset->update($data) : false;
    }
}
