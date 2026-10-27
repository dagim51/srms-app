<?php

namespace App\Models;

use App\Domains\Booking\Models\BookingGuest as DomainBookingGuest;

class_alias(DomainBookingGuest::class, 'App\Models\BookingGuest');
