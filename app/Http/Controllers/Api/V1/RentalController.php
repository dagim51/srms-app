<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Rental\Http\Controllers\Api\V1\RentalController as DomainRentalController;

class_alias(DomainRentalController::class, 'App\Http\Controllers\Api\V1\RentalController');
