<?php

namespace Modules\Sales\Providers;

use Modules\Sales\Domain\Contracts\CustomerRepositoryInterface;
use Modules\Sales\Domain\Contracts\DeliveryChallanRepositoryInterface;
use Modules\Sales\Domain\Contracts\LeadRepositoryInterface;
use Modules\Sales\Domain\Contracts\SalesOrderRepositoryInterface;
use Modules\Sales\Domain\Models\Customer;
use Modules\Sales\Domain\Models\DeliveryChallan;
use Modules\Sales\Domain\Models\Lead;
use Modules\Sales\Domain\Models\SalesOrder;
use Modules\Sales\Domain\Models\SalesOrderItem;
use Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories\EloquentCustomerRepository;
use Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories\EloquentDeliveryChallanRepository;
use Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories\EloquentLeadRepository;
use Modules\Sales\Infrastructure\Persistence\Eloquent\Repositories\EloquentSalesOrderRepository;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Customer::class,
        Lead::class,
        SalesOrder::class,
        SalesOrderItem::class,
        DeliveryChallan::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(LeadRepositoryInterface::class, EloquentLeadRepository::class);
        $this->app->bind(SalesOrderRepositoryInterface::class, EloquentSalesOrderRepository::class);
        $this->app->bind(DeliveryChallanRepositoryInterface::class, EloquentDeliveryChallanRepository::class);
    }
}
