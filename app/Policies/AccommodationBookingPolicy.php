<?php

namespace App\Policies;

use App\Domains\Booking\Policies\AccommodationBookingPolicy as DomainAccommodationBookingPolicy;

class_alias(DomainAccommodationBookingPolicy::class, 'App\Policies\AccommodationBookingPolicy');
