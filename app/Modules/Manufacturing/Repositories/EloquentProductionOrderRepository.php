<?php

namespace Modules\Manufacturing\Repositories;

use Modules\Manufacturing\Contracts\ProductionOrderRepositoryInterface;
use Modules\Manufacturing\Models\ProductionMaterialConsumption;
use Modules\Manufacturing\Models\ProductionOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EloquentProductionOrderRepository implements ProductionOrderRepositoryInterface
{
    public function findById(int $id): ?ProductionOrder
    {
        return ProductionOrder::with(['product', 'bom', 'sourceWarehouse', 'targetWarehouse', 'materialConsumptions.product'])->find($id);
    }

    public function findOrFail(int $id): ProductionOrder
    {
        return ProductionOrder::with(['product', 'bom', 'sourceWarehouse', 'targetWarehouse', 'materialConsumptions.product'])->findOrFail($id);
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = ProductionOrder::with(['product.uom', 'bom', 'sourceWarehouse', 'targetWarehouse']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    public function create(array $data, array $consumptions = []): ProductionOrder
    {
        return DB::transaction(function () use ($data, $consumptions) {
            $order = ProductionOrder::create($data);

            foreach ($consumptions as $item) {
                $item['production_order_id'] = $order->id;
                ProductionMaterialConsumption::create($item);
            }

            return $order->load('materialConsumptions');
        });
    }

    public function updateStatus(int $id, string $status, ?float $producedQuantity = null): ProductionOrder
    {
        $order = ProductionOrder::findOrFail($id);
        $payload = ['status' => $status];
        if ($producedQuantity !== null) {
            $payload['produced_quantity'] = $producedQuantity;
        }
        $order->update($payload);
        return $order;
    }
}
