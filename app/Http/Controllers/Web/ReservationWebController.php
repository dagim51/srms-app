<?php

namespace App\Http\Controllers\Web;

use App\Domains\Stay\Http\Controllers\Web\ReservationWebController as DomainReservationWebController;

class_alias(DomainReservationWebController::class, 'App\Http\Controllers\Web\ReservationWebController');
