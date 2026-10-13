<?php

namespace Modules\Assets\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AssetRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Asset;
    public function findByTag(string $tag): ?Asset;
    public function create(array $data): Asset;
    public function update(int $id, array $data): bool;
}
