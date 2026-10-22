<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Analytics\Http\Controllers\Api\V1\AnalyticsController as DomainAnalyticsController;

class_alias(DomainAnalyticsController::class, 'App\Http\Controllers\Api\V1\AnalyticsController');
