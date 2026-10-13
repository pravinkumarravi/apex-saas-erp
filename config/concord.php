<?php

return [
    'modules' => [
        \Modules\Organization\Providers\ModuleServiceProvider::class,
        \Modules\Identity\Providers\ModuleServiceProvider::class,
        \Modules\Governance\Providers\ModuleServiceProvider::class,
        \Modules\Inventory\Providers\ModuleServiceProvider::class,
        \Modules\Procurement\Providers\ModuleServiceProvider::class,
        \Modules\Manufacturing\Providers\ModuleServiceProvider::class,
        \Modules\Sales\Providers\ModuleServiceProvider::class,
        \Modules\Accounting\Providers\ModuleServiceProvider::class,
        \Modules\HumanResources\Providers\ModuleServiceProvider::class,
        \Modules\Projects\Providers\ModuleServiceProvider::class,
        \Modules\Assets\Providers\ModuleServiceProvider::class,
    ],
    'register_route_models' => true
];
