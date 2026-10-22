<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\PropertyManagement\Http\Controllers\Api\V1\PropertyManagementController as DomainPropertyManagementController;

class_alias(DomainPropertyManagementController::class, 'App\Http\Controllers\Api\V1\PropertyManagementController');
