<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Transaction\Http\Controllers\Api\V1\FinancialsController as DomainFinancialsController;

class_alias(DomainFinancialsController::class, 'App\Http\Controllers\Api\V1\FinancialsController');
