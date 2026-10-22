<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Analytics\Http\Controllers\Api\V1\ValuationController as DomainValuationController;

class_alias(DomainValuationController::class, 'App\Http\Controllers\Api\V1\ValuationController');
