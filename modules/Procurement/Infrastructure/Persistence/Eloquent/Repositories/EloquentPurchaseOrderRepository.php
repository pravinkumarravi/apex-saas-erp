<?php

namespace Modules\Procurement\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Procurement\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Procurement\Domain\Models\PurchaseOrder;
use Modules\Procurement\Domain\Models\PurchaseOrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EloquentPurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function findById(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::with(['supplier', 'warehouse', 'items.product'])->find($id);
    }

    public function findOrFail(int $id): PurchaseOrder
    {
        return PurchaseOrder::with(['supplier', 'warehouse', 'items.product'])->findOrFail($id);
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'creator', 'items.product']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->latest('order_date')->paginate($perPage);
    }

    public function create(array $data, array $items = []): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $po = PurchaseOrder::create($data);

            foreach ($items as $itemData) {
                $itemData['purchase_order_id'] = $po->id;
                PurchaseOrderItem::create($itemData);
            }

            return $po->load('items');
        });
    }

    public function updateStatus(int $id, string $status, ?int $approvedBy = null): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);
        $payload = ['status' => $status];
        if ($approvedBy !== null) {
            $payload['approved_by'] = $approvedBy;
        }
        $po->update($payload);
        return $po;
    }
}
