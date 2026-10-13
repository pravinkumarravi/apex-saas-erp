<?php

namespace Modules\Sales\Providers;

use Modules\Sales\Contracts\CustomerRepositoryInterface;
use Modules\Sales\Contracts\DeliveryChallanRepositoryInterface;
use Modules\Sales\Contracts\LeadRepositoryInterface;
use Modules\Sales\Contracts\SalesOrderRepositoryInterface;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\DeliveryChallan;
use Modules\Sales\Models\Lead;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Models\SalesOrderItem;
use Modules\Sales\Repositories\EloquentCustomerRepository;
use Modules\Sales\Repositories\EloquentDeliveryChallanRepository;
use Modules\Sales\Repositories\EloquentLeadRepository;
use Modules\Sales\Repositories\EloquentSalesOrderRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(LeadRepositoryInterface::class, EloquentLeadRepository::class);
        $this->app->bind(SalesOrderRepositoryInterface::class, EloquentSalesOrderRepository::class);
        $this->app->bind(DeliveryChallanRepositoryInterface::class, EloquentDeliveryChallanRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'sales');
            $this->loadViewsFrom($viewsPath, 'crm');
        }
    }
}
