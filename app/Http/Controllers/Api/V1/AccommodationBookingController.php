<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Http\Controllers\Api\V1\AccommodationBookingController as DomainAccommodationBookingController;

class_alias(DomainAccommodationBookingController::class, 'App\Http\Controllers\Api\V1\AccommodationBookingController');
