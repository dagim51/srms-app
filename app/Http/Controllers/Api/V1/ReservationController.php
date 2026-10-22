<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Stay\Http\Controllers\Api\V1\ReservationController as DomainReservationController;

class_alias(DomainReservationController::class, 'App\Http\Controllers\Api\V1\ReservationController');
