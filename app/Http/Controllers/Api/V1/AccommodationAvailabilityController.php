<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Http\Controllers\Api\V1\AccommodationAvailabilityController as DomainAccommodationAvailabilityController;

class_alias(DomainAccommodationAvailabilityController::class, 'App\Http\Controllers\Api\V1\AccommodationAvailabilityController');
