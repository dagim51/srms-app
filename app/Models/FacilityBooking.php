<?php

namespace App\Models;

use App\Domains\PropertyManagement\Models\FacilityBooking as DomainFacilityBooking;

class_alias(DomainFacilityBooking::class, 'App\Models\FacilityBooking');
