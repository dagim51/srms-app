<?php

namespace App\Models;

use App\Domains\Stay\Models\AccommodationAvailability as DomainAccommodationAvailability;

class_alias(DomainAccommodationAvailability::class, 'App\Models\AccommodationAvailability');
