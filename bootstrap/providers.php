<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,
    Modules\Organization\Providers\ModuleServiceProvider::class,
    Modules\Identity\Providers\ModuleServiceProvider::class,
    Modules\Governance\Providers\ModuleServiceProvider::class,
    Modules\Inventory\Providers\ModuleServiceProvider::class,
    Modules\Procurement\Providers\ModuleServiceProvider::class,
    Modules\Manufacturing\Providers\ModuleServiceProvider::class,
    Modules\Sales\Providers\ModuleServiceProvider::class,
    Modules\Accounting\Providers\ModuleServiceProvider::class,
    Modules\HumanResources\Providers\ModuleServiceProvider::class,
    Modules\Projects\Providers\ModuleServiceProvider::class,
    Modules\Assets\Providers\ModuleServiceProvider::class,
    Modules\QualityAudit\Providers\QualityAuditServiceProvider::class,
    Modules\RestApi\Providers\RestApiServiceProvider::class,
];
