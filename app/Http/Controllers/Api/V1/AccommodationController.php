<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Stay\Http\Controllers\Api\V1\AccommodationController as DomainAccommodationController;

class_alias(DomainAccommodationController::class, 'App\Http\Controllers\Api\V1\AccommodationController');
