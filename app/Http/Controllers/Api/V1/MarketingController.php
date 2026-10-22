<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Advertising\Http\Controllers\Api\V1\MarketingController as DomainMarketingController;

class_alias(DomainMarketingController::class, 'App\Http\Controllers\Api\V1\MarketingController');
