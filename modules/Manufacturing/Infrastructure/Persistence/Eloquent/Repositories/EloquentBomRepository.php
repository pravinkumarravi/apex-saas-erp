<?php

namespace Modules\Manufacturing\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Manufacturing\Domain\Contracts\BomRepositoryInterface;
use Modules\Manufacturing\Domain\Models\BillOfMaterial;
use Modules\Manufacturing\Domain\Models\BomItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EloquentBomRepository implements BomRepositoryInterface
{
    public function findById(int $id): ?BillOfMaterial
    {
        return BillOfMaterial::with(['product.category', 'items.product'])->find($id);
    }

    public function findOrFail(int $id): BillOfMaterial
    {
        return BillOfMaterial::with(['product.category', 'items.product'])->findOrFail($id);
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return BillOfMaterial::with(['product.category', 'items.product'])->latest()->paginate($perPage);
    }

    public function create(array $data, array $items = []): BillOfMaterial
    {
        return DB::transaction(function () use ($data, $items) {
            $bom = BillOfMaterial::create($data);

            foreach ($items as $itemData) {
                $itemData['bom_id'] = $bom->id;
                BomItem::create($itemData);
            }

            return $bom->load('items');
        });
    }
}
