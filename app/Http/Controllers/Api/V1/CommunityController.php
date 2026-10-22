<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\PropertyManagement\Http\Controllers\Api\V1\CommunityController as DomainCommunityController;

class_alias(DomainCommunityController::class, 'App\Http\Controllers\Api\V1\CommunityController');
