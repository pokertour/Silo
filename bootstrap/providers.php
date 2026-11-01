<?php

use App\Providers\AppServiceProvider;
use App\Providers\DynamicStorageServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\VoltServiceProvider;

return [
    AppServiceProvider::class,
    DynamicStorageServiceProvider::class,
    FortifyServiceProvider::class,
    VoltServiceProvider::class,
];
