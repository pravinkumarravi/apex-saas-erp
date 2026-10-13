<?php

namespace Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Sales\Domain\Contracts\SalesOrderRepositoryInterface;
use Modules\Sales\Domain\Models\SalesOrder;
use Modules\Sales\Domain\Models\SalesOrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EloquentSalesOrderRepository implements SalesOrderRepositoryInterface
{
    public function findById(int $id): ?SalesOrder
    {
        return SalesOrder::with(['customer', 'warehouse', 'items.product', 'deliveryChallans'])->find($id);
    }

    public function findOrFail(int $id): SalesOrder
    {
        return SalesOrder::with(['customer', 'warehouse', 'items.product', 'deliveryChallans'])->findOrFail($id);
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = SalesOrder::with(['customer', 'warehouse', 'items.product']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('order_date')->paginate($perPage);
    }

    public function create(array $data, array $items = []): SalesOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $so = SalesOrder::create($data);

            foreach ($items as $itemData) {
                $itemData['sales_order_id'] = $so->id;
                SalesOrderItem::create($itemData);
            }

            return $so->load('items');
        });
    }

    public function updateStatus(int $id, string $status): SalesOrder
    {
        $so = SalesOrder::findOrFail($id);
        $so->update(['status' => $status]);
        return $so;
    }
}
