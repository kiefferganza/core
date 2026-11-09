<?php

use Appetiser\Auth\Providers\ModuleServiceProvider;

return [
    'modules' => [
        /**
         * Example:
         * VendorA\ModuleX\Providers\ModuleServiceProvider::class,
         * VendorB\ModuleY\Providers\ModuleServiceProvider::class
         */
        ModuleServiceProvider::class,
        Appetiser\Admin\Providers\ModuleServiceProvider::class,
        Appetiser\Reporting\Providers\ModuleServiceProvider::class,
        Appetiser\Media\Providers\ModuleServiceProvider::class,
        Appetiser\Sms\Providers\ModuleServiceProvider::class,
        Appetiser\AppSettings\Providers\ModuleServiceProvider::class,

    ],
    'register_route_models' => true,
];
