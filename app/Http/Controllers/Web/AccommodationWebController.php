<?php

namespace App\Http\Controllers\Web;

use App\Domains\Stay\Http\Controllers\Web\AccommodationWebController as DomainAccommodationWebController;

class_alias(DomainAccommodationWebController::class, 'App\Http\Controllers\Web\AccommodationWebController');
