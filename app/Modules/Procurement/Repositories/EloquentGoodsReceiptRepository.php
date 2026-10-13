<?php

namespace Modules\Procurement\Repositories;

use Modules\Procurement\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Procurement\Models\GoodsReceiptItem;
use Modules\Procurement\Models\GoodsReceiptNote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EloquentGoodsReceiptRepository implements GoodsReceiptRepositoryInterface
{
    public function findById(int $id): ?GoodsReceiptNote
    {
        return GoodsReceiptNote::with(['purchaseOrder.supplier', 'warehouse', 'items.product', 'receiver'])->find($id);
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return GoodsReceiptNote::with(['purchaseOrder.supplier', 'warehouse', 'items.product', 'receiver'])
            ->latest('receipt_date')
            ->paginate($perPage);
    }

    public function create(array $data, array $items = []): GoodsReceiptNote
    {
        return DB::transaction(function () use ($data, $items) {
            $grn = GoodsReceiptNote::create($data);

            foreach ($items as $itemData) {
                $itemData['goods_receipt_note_id'] = $grn->id;
                GoodsReceiptItem::create($itemData);
            }

            return $grn->load('items');
        });
    }
}
