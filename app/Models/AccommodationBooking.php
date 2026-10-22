<?php

namespace App\Models;

use App\Domains\Booking\Models\AccommodationBooking as DomainAccommodationBooking;

class_alias(DomainAccommodationBooking::class, 'App\Models\AccommodationBooking');
