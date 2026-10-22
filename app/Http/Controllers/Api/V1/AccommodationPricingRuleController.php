<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Http\Controllers\Api\V1\AccommodationPricingRuleController as DomainAccommodationPricingRuleController;

class_alias(DomainAccommodationPricingRuleController::class, 'App\Http\Controllers\Api\V1\AccommodationPricingRuleController');
