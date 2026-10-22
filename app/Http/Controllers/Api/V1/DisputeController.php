<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Trust\Http\Controllers\Api\V1\DisputeController as DomainDisputeController;

class_alias(DomainDisputeController::class, 'App\Http\Controllers\Api\V1\DisputeController');
